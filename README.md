# KDE Weblate Translation

## Translation

* Signup for an account
* Choose a project and a component
* Start translating

**IMPORTANT** : After translation, add your name to `Your names` (`NAME OF TRANSLATORS`) and email to `Your emails`. If there already exist a value, add yours after a comma. Example :

* Your names :
  ```
  ശ്യാം കൃഷ്ണന്‍ സി.ആര്‍.,സുബിന്‍ സിബി
  ```
* Your emails :
  ```
  shyam@example.com,subin@example.com
  ```

## Setup

* Clone repo
* Install :
  ```
  sudo apt install libpq-dev # https://stackoverflow.com/a/12037133/1372424
  export PIPENV_VENV_IN_PROJECT=1
  pipenv install
  ```

* Make `.env`
  ```
  pipenv shell
  ln -s $(realpath settings.py) .venv/lib/python3.6/site-packages/weblate/settings.py
  ```
* Setup Redis & Celery :
  ```
  sudo apt install redis-server
  .venv/lib/python3.6/site-packages/weblate/examples/celery start
  ```

### Importing components to Weblate

* Create a project : `kde`
* Import each files as components from the [git repo](https://github.com/FOSSersVAST/kde-ml) :
  ```
  weblate import_project kde 'https://github.com/FOSSersVAST/kde-ml.git' master "l10n-kf5/(?P<language>[^/]*)/(?P<component>[^-]*)\.po"
  ```
* Configure the project to have a SSH key for pushing to `git@github.com:FOSSersVAST/kde-ml.git`. Add the ssh key as a deploy key in GitHub repo.

## Mirror git repo

These things should be used in the [mirror git repo](https://github.com/FOSSersVAST/kde-ml). The mirror gir repo has the structure :

* l10n-kf5
  * ml
    * applications
    * kde-workspace
    * ...
  * templates
    * applications
    * kde-workspace
    * ...
* README.md
* ...

Here, the folder `applications`, `kde-workspace` are all actually SVN cloned folders :

```
cd l10n-kf5/ml
svn co svn+ssh://svn@svn.kde.org/home/kde/branches/stable/l10n-kf5/ml/messages/applications applications
cd l10n-kf5/templates
svn co svn+ssh://svn@svn.kde.org/home/kde/branches/stable/l10n-kf5/templates/messages/applications applications
```

So basically, we're tracking these SVN repo files in `git`. The `.svn` folders are ignored in `.gitignore`.

### Updating to KDE Upstream

We're gonna localize **only** the trunk branch in KDE upstream.

* The `master` branch must be kept up-to-date with KDE upstream (trunk). **No merges from other git branches should be done here**.
* Work should be done on `weblate` branch (trunk). When `master` is synced to upstream, do 
  ```
  git checkout weblate
  git merge --no-ff master
  ```

To sync files with upstream :
```
export REPO_ROOT=$PWD
export LANG_CODE='ml'

for PACKAGE in $(ls $REPO_ROOT/l10n-kf5/templates); do
  echo $PACKAGE
  cd $REPO_ROOT/l10n-kf5/templates/$PACKAGE && svn revert . -R && svn update --accept theirs-full
  cd $REPO_ROOT/l10n-kf5/$LANG_CODE/$PACKAGE && svn revert . -R && svn update --accept theirs-full
done
```

We're using `svn revert` to [make sure](https://stackoverflow.com/questions/840509/svn-update-is-not-updating) every file is same as upstream.

Then commit,
```
git commit -a -m "Sync with KDE Upstream"
```

Better add a [webhook in GitHub to Weblate](https://docs.weblate.org/en/latest/admin/continuous.html#automatically-receiving-changes-from-github) so that Weblate is known of the changes automatically. Do this with the `weblate` branch.

Or go to Weblate admin, choose the project and do action "Update VCS repository".

### Pushing to KDE Upstream

First, we need to committ Weblate changes to the Mirror git repo. Then we push from the git repo to SVN.

* Go to Weblate admin webpage, select the project, **Pull changes** and then do action **Commit changes**.
* In the localization maintainer's cloned mirror git repo :
  ```
  git checkout weblate # Make sure branch is weblate
  git pull
  ```
* Go to each folder and commit to SVN :
  ```
  cd l10n-kf5/ml/applications
  svn commit -m 'Update Malayalam localizations'
  ```

### Merging trunk & stable

Work is done on trunk branch and similar localizations from it are merged to stable. There will be two folders, `l10n-kf5` for trunk branch of KDE Framework 5 and `stable-kf5` for the stable branch (which we will clone).

* [Pull all changes to trunk](#committing-pootle-changes-to-mirror-git-repo)
* In the mirror git repo, checkout a new branch for our temporary work (this branch will be deleted at the end) :
  ```
  git checkout -b stable
  mkdir stable-kf5
  ```
* Clone the [KDE upstream](#committing-to-kde-upstream)'s stable branch :
  ```
  cd stable-kf5
  svn co svn+ssh://svn@svn.kde.org/home/kde/branches/stable/l10n-kf5/ml/messages ml
  ```
  The folder structure will be like :
  ```
  * l10n-kf5
    * ml
      * applications
    * templates
  * stable-kf5
    * ml
      * applications
      * kde-workspace
      * ...
  ```
* Run the `merge-to-stable.sh` script