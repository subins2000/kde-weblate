# Weblate Instance

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