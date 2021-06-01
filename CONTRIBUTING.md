# CONTRIBUTING

## RESOURCES

If you wish to contribute to this project, please be sure to read/subscribe to the following resources:

- [Coding Standards](https://github.com/laminas/laminas-coding-standard)
- [Forums](https://discourse.laminas.dev/c/contributors)
- [Chat](https://laminas.dev/chat)
- [Code of Conduct](CODE_OF_CONDUCT.md)

If you are working on new features or refactoring, [create a proposal](./issues/new?labels=RFC&template=RFC.md&title=%5BRFC%5D%3A+%5BTITLE+OF+RFC%5D).

## RUNNING TESTS

To run tests:

- Clone the repository.
  Click the "Clone or download" button on the repository to find both the URL and instructions on cloning.

- Install dependencies via composer:

  ```console
  $ composer install
  ```

  If you don't have `composer` installed, please download it from https://getcomposer.org/download/

- Run the tests using the "test" command shipped in the `composer.json`:

  ```console
  $ composer test
  ```

You can turn on conditional tests with the `phpunit.xml` file.  To do so:

- Copy the `phpunit.xml.dist` file to `phpunit.xml`
- Edit the `phpunit.xml` file to enable any specific functionality you want to test, as well as to provide test values to utilize.

## Running Coding Standards Checks

First, ensure you've installed dependencies via composer, per the previous section on running tests.

To run CS checks only:

```console
$ composer cs-check
```

To attempt to automatically fix common CS issues:

```console
$ composer cs-fix
```

If the above fixes any CS issues, please re-run the tests to ensure they pass, and make sure you add and commit the changes after verification.

## Release Branches

Each repository uses _release branches_ named after the minor release series they represent; e.g., "2.11.x", "3.1.x", "0.2.x", etc.
Each branch is under a "security window", and can and will receive security updates as long as the minimum supported version of PHP in that branch is still supported by php.net.
Generally speaking, the default branch is targeting the next minor or major version, and does not yet have releases against it; it represents new features or changes to the package.
The release branch with the next most recent release series is the one for the currently active release, and will receive bugfixes and security fixes, but no new features.
Since each component has its own lifecycle, the number and names of releases branches will vary from component to component.

## Recommended Workflow for Contributions

Your first step is to establish a public repository from which we can pull your work into the canonical repository.
We recommend using [GitHub](https://github.com), as that is where the component is already hosted.

1. Setup a [GitHub account](https://github.com/join), if you haven't yet
2. Fork the repository using the "Fork" button at the top right of the repository landing page.
3. Clone the canonical repository locally.
   Use the "Clone or download" button above the code listing on the repository landing pages to obtain the URL and instructions.
4. Navigate to the directory where you have cloned the repository.
5. Add a remote to your fork; substitute your GitHub username and the repository name in the commands below.

   ```console
   $ git remote add fork git@github.com:{username}/{repository}.git
   $ git fetch fork
   ```

Alternately, you can use the [GitHub CLI tool](https://cli.github.com) to accomplish these steps:

```console
$ gh repo clone {org}/{repo}
$ cd {repo}
$ gh repo fork
```

### Keeping Up-to-Date

Periodically, you should update your fork or personal repository to match the canonical repository.
Assuming you have setup your local repository per the instructions above, you can do the following:

```console
$ git fetch origin
$ git switch {branch to update}
$ git pull --rebase --autostash
# OPTIONALLY, to keep your remote up-to-date -
$ git push fork {branch}:{branch}
```

If you're tracking other release branches, you'll want to do the same operations for each branch.

### Working on a patch

We recommend you do each new feature or bugfix in a new branch.
This simplifies the task of code review as well as the task of merging your changes into the canonical repository.

A typical workflow will then consist of the following:

1. Create a new local branch based off the appropriate release branch.
2. Switch to your new local branch.
   (This step can be combined with the previous step with the use of `git switch -c {new branch} {original branch}`, or, if the original branch is the current one, `git switch -c {new branch}`.)
3. Do some work, commit, repeat as necessary.
4. Push the local branch to your remote repository.
5. Send a pull request.

The mechanics of this process are actually quite trivial. Below, we will
create a branch for fixing an issue in the tracker.

```console
$ git switch -c hotfix/9295
Switched to a new branch 'hotfix/9295'
```

... do some work ...


```console
$ git commit -s
```

> ### About the -s flag
>
> See the [section on commit signoffs](#commit-signoffs) below for more details on the `-s` option to `git commit` and why we require it.

... write your log message ...

```console
$ git push fork hotfix/9295:hotfix/9295
Counting objects: 38, done.
Delta compression using up to 2 threads.
Compression objects: 100% (18/18), done.
Writing objects: 100% (20/20), 8.19KiB, done.
Total 20 (delta 12), reused 0 (delta 0)
To ssh://git@github.com/{username}/laminas-zendframework-bridge.git
   b5583aa..4f51698  HEAD -> hotfix/9295
```

To send a pull request, you have several options.

If using GitHub, you can do the pull request from there.
Navigate to your repository, select the branch you just created, and then select the "Pull Request" button in the upper right.
Select the user/organization "laminas" (or whatever the upstream organization is) as the recipient.

You can also perform the same steps via the [GitHub CLI tool](https://cli.github.com).
Execute `gh pr create`, and step through the dialog to create the pull request.
If the branch you will submit against is not the default branch, use the `-B {branch}` option to specify the branch to create the patch against.

#### What branch to issue the pull request against?

Which branch should you issue a pull request against?

- For fixes against the stable release, issue the pull request against the release branch matching the minor release you want to fix.
- For new features, or fixes that introduce new elements to the public API (such as new public methods or properties), issue the pull request against the default branch.

### Branch Cleanup

As you might imagine, if you are a frequent contributor, you'll start to get a ton of branches both locally and on your remote.

Once you know that your changes have been accepted to the canonical repository, we suggest doing some cleanup of these branches.

- Local branch cleanup

  ```console
  $ git branch -d <branchname>
  ```

- Remote branch removal

  ```console
  $ git push fork :<branchname>
  ```

## Contributing Documentation

The documentation for each repository is available in the "**docs/**" directory of that repository, is written in [Markdown format], and is built using [MkDocs].
To learn more about how to contribute to the documentation for a repository, including how to setup the documentation locally, please refer to https://github.com/laminas/documentation.

## Commit Signoffs

In order for us to accept your patches, you must provide a signoff in all commits; this is done by using the `-s` or `--signoff` option when calling `git commit`.
The signoff is used to certify that you have rights to submit your patch under the project's license, and that you agree to the [Developer Certificate of Origin](https://developercertificate.org).

### Automating signoffs

You can automate adding your signoff by using a commit template that includes the line:

```text
Signed-off-by: Your Name <your.email@example.com>
```

Put that line into a file, and then run the command:

```console
git config commit.template {path to file}
```

If you want to use this template everywhere, pass the `--global` option to `git config`.

### Adding signoffs when committing from the GitHub website

You can add a signoff manually when committing from the GitHub website by adding the line `Signed-off-by: Your Name <your.email@example.org>` by itself in the commit message.

### Adding signoffs after-the-fact

If you have forgotten to signoff your commits, you have two options.
For both, the first step is determining the number of commits you have made in your patch.
On Unix-like systems (Linux, BSD, OSX, WSL, etc.), this is generally accomplished via:

```console
git log --oneline {original branch}..HEAD | wc -l
```

From there, choose either to only signoff, or perform an interactive rebase:

- Signoff only: run `git rebase --signoff HEAD~{number you discovered}`

- Full interactive rebase: run `git rebase -i HEAD~{number you discovered} -x "git commit --amend --signoff --no-edit"`.
  This will present the standard interactive rebase screen, but include the line `exec git commit --amend --signoff --no-edit` between each commit.
  Leave those lines after each commit you will be keeping.

If you run into issues during a rebase operation, you can generally execute `git rebase --abort` to return to the original state.

When done, execute `git push -f`, specifying the correct remote and branch, in order to force-push your amendments.

### Obvious Fix Policy

Small contributions, such as fixing spelling errors, where the content is small enough to not be considered intellectual property, can be submitted without signing the contribution for the DCO.

As a rule of thumb, changes are obvious fixes if they do not introduce any new functionality or creative thinking.
Assuming the change does not affect functionality, some common obvious fix examples include the following:

- Spelling / grammar fixes.
- Typo correction, white space, and formatting changes.
- Comment clean up.
- Bug fixes that change default return values or error codes stored in constants.
- Adding or changing exception messages.
- Changes to 'metadata' files like `.gitignore`, `composer.json`, etc.
- Moving source files from one directory to another.

Whenever you invoke the "obvious fix" rule, please say so in your commit message:

```text
commit 87b503f5e190e13359d4abb3e2fccd04e949d8df
Author: I.M. Developer <imdeveloper@getlaminas.org>
Date:   Wed Apr 01 14:46:40 2020 -0500

  Fix typo in the README.

  Obvious fix.
```

## Mass contributions

Occasionally, a patch or set of changes may need to be propagated to multiple repositories/packages.
As some past examples:

- Rolling out the GitHub Actions automatic-releases workflow.
- Rolling out the GitHub Actions continuous-integration workflow.
- Updating repositories to laminas-coding-standard (or a later revision of laminas-coding-standard).

In all such cases, contributors MUST get approval from the [Technical Steering Committee](https://github.com/laminas/technical-steering-committee) PRIOR to submitting the patches.
The reasons for this include:

- Ensuring no duplication of effort occurs.
- Ensuring the changes have been discussed and approved by the TSC.
- Ensuring there will be maintainers available to review and merge the patches.

We do not want to waste either maintainer or contributor time, so coordinating such changes, while it may seem like an extra step, can save everybody time and effort in the long run.

If you are unsure whether to start pushing patches to all repositories, please [open an RFC with the Technical Steering Committee](https://github.com/laminas/technical-steering-committee/issues/new).
If you need assistance doing so, ask in the #contributors channel.

[MkDocs]: https://www.mkdocs.org/
[Markdown format]: https://www.markdownguide.org/