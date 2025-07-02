# Contributing to Phug

## Code of Conduct

We worked hard to provide this pug port to you for free.
That's why we ask you to be polite and respectful. For
example, when you report an issue, please use human-friendly
sentences ("Hello", "Please", "Thanks", etc.)

## Issue Contributions

Please report any security issue or risk by emailing phug@selfbuild.fr.
Please don't disclose security bugs publicly until they have been
handled by us.

For any other bug or issue, you should first have a look at our
fancy interactive documentation:
(phug-lang.com)[https://phug-lang.com/] and in particular
(the frequently asked questions section)[https://phug-lang.com/#frequently-asked-questions].

If the solution of your problem is not in the documentation, please
click this link and follow the template if applicable:
[Create new issue](https://github.com/phug-php/phug/issues/new)

This template will help your provide us the informations we need for
most issues (the PHP and/or Pug code you use, the expected behaviour
and the current behaviour).

## Code Contributions

Fork the [GitHub project](https://github.com/phug-php/phug) and chek
out your copy locally:

```shell
git clone https://github.com/<username>/phug.git
cd phug
git remote add upstream https://github.com/phug-php/phug.git
```
Replace `<username>` with your GitHub username.

Then, you can work on the master or create a specific branch for your
development:

```shell
git checkout -b my-feature-branch -t origin/master
```

You can now edit the "phug" directory contents.

Before committing, please set your name and your e-mail (use the same
e-mail address as in your GitHub account):

```shell
git config --global user.name "Your Name"
git config --global user.email "your.email.address@example.com"
```

The ```--global``` argument will apply this setting for all your git
repositories, remove it to set only your phug fork with them.

Now you can index and commit your modifications as you usually do with git:

```shell
git add --all
git commit -m "The commit message log"
```

If your patch fixes an open issue, please insert ```#``` immediately
followed by the issue number:

```shell
git commit -m "#21 Fix this or that"
```

Use git rebase (not git merge) to sync your work from time to time:

```shell
git fetch origin
git rebase origin/master
```

Please add some tests for bug fixes and features (for example a pug file
and the expected HTML file in /tests/templates), then check all is right
with phpunit:

Install PHP if you have'nt yet, then install composer:
https://getcomposer.org/download/

Update dependencies:
```
./composer.phar update
```

Or if you installed composer globally:
```
composer update
```

Then call phpunit:
```
./vendor/bin/phpunit
```

Make sure all tests succeed before submit your pull-request, else we will
not be able to merge it.

Push your work on your remote GitHub fork with:
```
git push origin my-feature-branch
```

Go to https://github.com/yourusername/phug and select your feature branch.
Click the 'Pull Request' button and fill out the form.

We will review it within a few days. And we thank you in advance for your help.
