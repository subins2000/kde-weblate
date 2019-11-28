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

* KDE l10n uses SVN :
  ```
  sudo apt install git-svn
  ```
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

## Mirror git repo

These things should be used on the [mirror git repo](https://github.com/FOSSersVAST/kde-ml).

### Updating to KDE Upstream

The `master` branch must be kept up-to-date with KDE upstream. **No merges from other git branches should be done here**.

For trunk **localization branch** :
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

Better add a [webhook in GitHub to Weblate](https://docs.weblate.org/en/latest/admin/continuous.html#automatically-receiving-changes-from-github) so that Weblate is known of the changes automatically.

Or go to Weblate admin, choose the project and do action "Update VCS repository".

### Committing Weblate changes to Mirror git repo

Do these in the cloned mirror git repo folder in server (`data/vcs/?`).

* Go to Weblate admin webpage, select the project and do action "Commit changes".
* Switch to `pootle` branch, merge upstream changes and sync :
  ```
  git checkout pootle
  git merge --no-ff master
  pootle fs sync l10n-kf5
  # Maybe have to do add & fetch
  # pootle fs add l10n-kf5
  # pootle fs fetch l10n-kf5
  # pootle fs sync l10n-kf5
  ```
  This will pull changes from Pootle to files
* Update file headers :
  ```
  bash update-changed-pos-header.sh
  ```
* Commit and push :
  ```
  git commit -a -m "Updates $(date)"
  git push origin pootle
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

### Committing to KDE upstream

* Get developer access to KDE SVN
* Checkout PO files :
  ```bash
  svn co svn+ssh://svn@svn.kde.org/home/kde/trunk/l10n-kf5/ml/messages
  ```
* Copy files from Mirror git repo to the checked out PO files
* Add files and commit
  ```
  # Add all files
  svn status | grep '?' | sed 's/^.* /svn add /' | bash
  svn commit -m 'Update malayalam localizations'
  ```