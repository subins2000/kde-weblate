# KDE Weblate Translation

## Translation

* [Signup for an account](//kde.smc.org.in)
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

## Workflow

There are 3 repos :

* KDE Upstream repo (SVN)
* Intermediary repo (git) : [This repo](https://github.com/FOSSersVAST/kde-pos).
* Weblate local repo (git) : Local copy of the intermediary repo

We need to make the intermediary repo and then setup Weblate.

Weblate will work with the intermediary repo, and we'll have to manually merge the intermediary repo with KDE upstream (not that much of difficulty).

## Setup

If your language team is not using Summit, use it. [Instructions here](https://github.com/subins2000/kde-weblate/blob/master/SETTING-UP-SUMMIT.md).

Using summit helps you to maintain only one branch for both trunk and stable.

### Intermediary repo

We need to create a new git repo which will act as an intermediary between KDE upstream repo (SVN) and Weblate.

Weblate will use this intermediary git repo for syncing.

The intermediary repo after the setup will have this folder structure :

* l10n-kf5
  * ml
    * dolphin
    * kde-workspace
    * ...
* upstream
  * l10n-kf5-summit
    * ml
      * dolphin
      * kde-workspace
      * ...
    * templates
* README.md
* ...

Make the upstream directory structure :

```
mkdir upstream upstream/l10n-kf5-summit
cd upstream/l10n-kf5-summit
svn co svn+ssh://svn@svn.kde.org/home/kde/trunk/l10n-support/ml/summit/messages ml
svn co svn+ssh://svn@svn.kde.org/home/kde/trunk/l10n-support/templates/summit/messages templates
```

Then, make the folder `l10n-kf5` in the root, and copy files from `upstream` folder with the exact sub-directory structure. For example, if you want to add Dolphin file manager (`dolphin`), then :

```
mkdir l10n-kf5 l10n-kf5/ml l10n-kf5/ml/dolphin
cp "upstream/l10n-kf5-summit/ml/dolphin/*" "l10n-kf5/ml/dolphin/"
```

### Weblate

* Clone this repo
* Install :
  ```
  sudo apt install libpq-dev # https://stackoverflow.com/a/12037133/1372424
  sudo apt install libacl1-dev
  export PIPENV_VENV_IN_PROJECT=1
  pipenv install
  ```
* Make `.env` :
  ```
  cp .env.example .env
  ```
  Edit the new `.env` and set a [Django secret key](https://djecrety.ir/), database credentials and optional other fetures.
* Get into the environment and let Weblate know the settings :
  ```
  pipenv shell
  ln -s $(realpath settings.py) .venv/lib/python3.6/site-packages/weblate/settings.py
  ```
* Setup Redis & Celery :
  ```
  sudo apt install redis-server
  .venv/lib/python3.6/site-packages/weblate/examples/celery start
  ```
* Create admin user :
  ```
  weblate createsuperuser
  ```
* Set domain in Django Admin -> Sites
* You need to update Weblate's plural form to accomodate with scripty's choice because scripty will change it to `(n != 1)` back everytime and that's a waste of git & svn storage. [Relevant](https://github.com/WeblateOrg/weblate/commit/56ee242b2c73aa1b892693c44d05c715b51832dd#diff-f45fc79cca287d720000daa62524df92)
  ```
  psql
  UPDATE lang_plural SET formula='(n != 1)' WHERE formula='n != 1'
  ```

#### Importing components to Weblate

Each PO file will be a component in Weblate. These components will be under a common project named `kde`.

* Create a project named `kde` using Weblate web interface
* [Setup the intermediary repo](#intermediary-repo)
* Configure the project to have a SSH key for pushing to the intermediary git repo. If it's a GitHub repo, you can add the ssh key as a deploy key in GitHub repo with write permission.
* From the server console, tell Weblate to import from the intermediary repo to `kde` project :
  ```
  weblate import_project kde 'https://github.com/FOSSersVAST/kde-pos.git' master "l10n-kf5/(?P<language>[^/]*)/(?P<component>[^%]*)\.po" 
  ```
* Set license of components :
  ```
  psql
  UPDATE trans_component SET license='Under the same license as the package', new_lang='none';
  ```
* We're gonna enable [suggestions voting](https://docs.weblate.org/en/latest/admin/translating.html#suggestion-voting). Set suggestions for all components & vote count to 3. Disable translation propagation (because it messess with `Your names` and `Your emails` strings)
  ```
  weblate shell -c 'from weblate.trans.models import Component; Component.objects.all().update(suggestion_voting=True, suggestion_autoaccept=3, allow_translation_propagation=False)'
  ```
* Restart Weblate
  ```
  .venv/lib/python3.7/site-packages/weblate/examples/celery restart
  ```
* Then do
  ```
  weblate loadpo kde
  ```
* Make project suggestion-review based. Go to Weblate, project page -> Users :
  * Project Access Control : Protected
  * Enable Reviews : Yes
  With this, you will have to specifically allow users to submit suggestions. Registered users can't submit suggestions by default. Special reviewer users can be assigned to accept suggestions. Or all users who can suggest can vote on it and get it accepted.

#### Social Auth

* [Follow this](https://docs.weblate.org/en/latest/admin/auth.html)
* Get API key & Secret, store in `.env` :
  ```
  SOCIAL_AUTH_GOOGLE_OAUTH2_KEY=''
  SOCIAL_AUTH_GOOGLE_OAUTH2_SECRET=''
  ```

## Intermediary Repo

### Syncing With KDE Upstream

NOTE: Don't make any change directly in the intermediary repo's `upstream` folder. If doing so, make sure to update the PO file in the `l10n-kf5` folder too.

* Go to Weblate web interface -> Repository Maintenance. Click `Commit` & `Push` buttons, one after the other.

  If there's any merge conflict, fix it in the repo on server. The git folder on repo will be at `data/vcs/<name>`. After fixing it, do a `git pull` to make sure everything's alright. And repeat this step (do Pull changes on Webalte, commit & push).
* Do a `git pull` in the intermediary repo
* Run
  ```bash
  copy-to-upstream.sh
  ```
  This will copy new localized strings from the recently pushed Weblate changes to KDE upstream summit repo.
* In `upstream/l10n-kf5-summit/ml` folder, commit :
  ```bash
  svn commit -m 'Updates from Weblate'
  ```
  See [SVN tips](#svn-tips)
* In **maintainer's local summit setup**, do : ([More details here](https://github.com/subins2000/kde-weblate/blob/master/SETTING-UP-SUMMIT.md#summit-next-steps))
  ```
  cd $KDEREPO
  svn update
  posummit $KDEREPO/trunk/l10n-support/scripts/messages.summit $KDEREPO/trunk/l10n-support/ml merge
  posummit $KDEREPO/trunk/l10n-support/scripts/messages.summit $KDEREPO/trunk/l10n-support/ml scatter

  svn commit $KDEREPO/trunk/l10n-support/ml $KDEREPO/branches/stable/ $KDEREPO/trunk/l10n-kf5/ml -m 'Routine Merge & Scatter'
  ```
* In intermediary repo's `upstream/l10n-kf5-summit/ml`, do
  ```
  svn update
  ```
* Copy files from `upstream` folder to intermediary repo's `l10n-kf5`
  ```bash
  copy-from-upstream.sh
  ```
  The script will only `cp` files that exists both in `upstream/l10n-kf5-summit` and `l10n-kf5` folders.
* Commit & push
  ```
  git commit -a -m "Sync with KDE Upstream"
  git push
  ```
* Go to Weblate web interface -> Repository Maintenance and Pull.

Steps till **Weblate pull changes** can be done periodically to keep Weblate POs up-to-date with upstream.

Better add a [webhook in GitHub to Weblate](https://docs.weblate.org/en/latest/admin/continuous.html#automatically-receiving-changes-from-github) so that Weblate is known of the changes automatically. Do this with the `weblate` branch.

![Illutsration](https://raw.githubusercontent.com/subins2000/kde-weblate/master/sync-flow.svg)

## SVN Tips

If you see a `?` next to files when doing `svn status`, then those files are untracked. You can track them all with :

```
# ? means file is new
# You may wanna add new files too (this is the equivalent of git add --all) :
svn status | grep '?' | sed 's/^.* /svn add /' | bash
```
