[Cherry picking commits](http://git-scm.com/docs/git-cherry-pick) is like doing small rebases (with one commit only)
but it moves current branch forward. Therefore, the easiest way of passing
this exercise was to git cherry-pick feature-a, feature-b and feature-c
consecutively.

However, as you should have noticed, cherry picks may lead to conflicts, too.
When you tried to pick feature-c, Git should have complained that it does not
know where to get first part of Feature C from (cherry-pick picks only one commit).
Therefore, it is often good idea to squash commits first before cherry-picking them
to other branch.