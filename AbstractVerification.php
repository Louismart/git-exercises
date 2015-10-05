<?php

require __DIR__ . '/VerificationFailure.php';
require __DIR__ . '/GitUtils.php';

abstract class AbstractVerification
{
    protected $oldRev;

    protected $newRev;

    public function __construct($oldRev, $newRev)
    {
        $this->oldRev = $oldRev;
        $this->newRev = $newRev;
    }

    public function verify()
    {
        $this->doVerify();
    }

    public function getHints()
    {
        $hintsFile = __DIR__ . '/hints/' . get_class($this) . '.txt';
        if (file_exists($hintsFile)) {
            return file_get_contents($hintsFile);
        }
    }

    public abstract function getShortInfo();

    protected abstract function doVerify();

    protected function ensure($condition, $errorMessage, array $formatVars = [])
    {
        if (!$condition) {
            throw new VerificationFailure($errorMessage, $formatVars);
        }
    }

    protected function ensureCommitsCount($count)
    {
        $commits = $this->getCommits();
        $this->ensure(count($commits) == $count, 'Expected number of commits: %d. Received %d.', [$count, count($commits)]);
        return $count == 1 ? $commits[0] : $commits;
    }

    protected function ensureFilesCount($commitId, $count)
    {
        $files = $this->getFilenames($commitId);
        $this->ensure(count($files) == $count, 'Commit %s should contain %d files. %d received.', [substr($commitId, 0, 7), $count, count($files)]);
        return $count == 1 ? $files[0] : $files;
    }

    public function getCommiterName($commitId = null)
    {
        return GitUtils::getCommiterName($commitId ? $commitId : $this->newRev);
    }

    protected function getCommits()
    {
        return GitUtils::getCommitIdsBetween($this->oldRev, $this->newRev);
    }

    protected function getFilenames($commitId)
    {
        return array_keys(GitUtils::getChangedFiles($commitId));
    }

    protected function getFileContent($commitId, $filePath)
    {
        return GitUtils::getFileContent($commitId, $filePath);
    }

    public static function factory($branch, $oldRev, $newRev)
    {
        $verificationName = ucfirst(self::dashToCamelCase(basename($branch)));
        @include __DIR__ . '/verifications/' . $verificationName . '.php';
        if (!class_exists($verificationName)) {
            throw new InvalidArgumentException('Wrong excercise.');
        }
        return new $verificationName($oldRev, $newRev);
    }

    private static function dashToCamelCase($name)
    {
        return preg_replace_callback('/-(.?)/', function ($matches) {
            return ucfirst($matches[1]);
        }, $name);
    }
}