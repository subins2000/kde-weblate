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

### Intermediary repo

We need to create a new git repo which will act as an intermediary between KDE upstream repo (SVN) and Weblate.

Weblate will use this intermediary git repo for syncing.

The intermediary repo after the setup will have this folder structure :

* l10n-kf5
  * ml
    * applications
    * kde-workspace
    * ...
* upstream
  * l10n-kf5-trunk
    * ml
      * applications
      * kde-workspace
      * ...
    * templates
  * stable-kf5
* README.md
* ...

Make the upstream directory structure :

```
mkdir upstream upstream/l10n-kf5-trunk
cd upstream/l10n-kf5-trunk
svn co svn+ssh://svn@svn.kde.org/home/kde/trunk/l10n-kf5/ml/messages ml
svn co svn+ssh://svn@svn.kde.org/home/kde/trunk/l10n-kf5/templates/messages templates
```

Then, make the folder `l10n-kf5` in the root, and copy files from `upstream` folder with the exact sub-directory structure. For example, if you want to add Dolphin file manager (`dolphin.po`), then :

```
mkdir l10n-kf5 l10n-kf5/ml l10n-kf5/ml/applications
cp "upstream/l10n-kf5-trunk/ml/applications/dolphin.po" "l10n-kf5/ml/applications/dolphin.po"
```

You may also make `upstream/stable-kf5` folder :

```
mkdir upstream/stable-kf5
cd upstream/stable-kf5
svn co svn+ssh://svn@svn.kde.org/home/kde/branches/stable/l10n-kf5/ml/messages ml
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
* Set domain in Django Admin -> Sites
* You need to update Weblate's plural form to accomodate with scripty's choice because scripty will change it to `(n != 1)` back everytime and that's a waste of git & svn storage. [Relevant](https://github.com/WeblateOrg/weblate/commit/56ee242b2c73aa1b892693c44d05c715b51832dd#diff-f45fc79cca287d720000daa62524df92)
  ```
  mysql -u root -p
  USE dbname;
  UPDATE lang_plural SET equation='(n != 1)' WHERE equation='n != 1'
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
  UPDATE trans_component SET license='Under the same license as the package', new_lang='none';
  ```
* We're gonna enable [suggestions voting](https://docs.weblate.org/en/latest/admin/translating.html#suggestion-voting). Set suggestions for all components & vote count to 3. Disable translation propagation (because it messess with `Your names` and `Your emails` strings)
  ```
  weblate shell -c 'from weblate.trans.models import Component; Component.objects.all().update(suggestion_voting=True, suggestion_autoaccept=3, allow_translation_propagation=False)'
  ```
* Then do
  ```
  weblate loadpo kde
  ```
* Make project suggestion-review based. Go to Weblate, project page -> Users :
  * Project Access Control : Protected
  * Enable Reviews : Yes

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

* In `upstream/l10n-kf5-trunk/ml`, do
  ```
  svn update
  ```
* Copy files from `upstream` folder to intermediary repo's `l10n-kf5`
  ```bash
  copy-from-upstream.sh
  ```
  The script will only `cp` files that exists both in `upstream/l10n-kf5-trunk` and `l10n-kf5` folders.
* Commit & push
  ```
  git commit -a -m "Sync with KDE Upstream"
  ```
* Go to Weblate web interface -> Repository Maintenance. Click `Pull changes`, `commit` & `push` buttons, one after the other
* Do a `git pull` in the intermediary repo
* Run
  ```bash
  copy-to-upstream.sh
  ```
  This will copy new localized strings from the recently pushed Weblate changes to KDE upstream repo.
* In `upstream/l10n-kf5-trunk/ml` folder, commit :
  ```bash
  svn commit -m 'Update malayalam'
  ```
  See [SVN tips](#svn-tips)

Steps till **Weblate pull changes** can be done periodically to keep Weblate POs up-to-date with upstream.

Better add a [webhook in GitHub to Weblate](https://docs.weblate.org/en/latest/admin/continuous.html#automatically-receiving-changes-from-github) so that Weblate is known of the changes automatically. Do this with the `weblate` branch.

## SVN Tips

If you see a `?` next to files when doing `svn status`, then those files are untracked. You can track them all with :

```
# ? means file is new
# You may wanna add new files too (this is the equivalent of git add --all) :
svn status | grep '?' | sed 's/^.* /svn add /' | bash
```
