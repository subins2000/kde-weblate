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
  export PIPENV_VENV_IN_PROJECT=1
  pipenv install
  ```

* Make `.env`
  ```
  pipenv shell
  ln -s $(realpath settings.py) .venv/lib/python3.6/site-packages/weblate/settings.py
  ```

## Adding a component

Example `applications`

* SVN Source Code repo : `svn://svn@svn.kde.org/home/kde/trunk/l10n-kf5/ml/messages/applications/`
* File mask : `*.po`