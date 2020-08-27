The folder structure in upstream had significantly changed on 2020 May migration. Info - https://marc.info/?l=kde-i18n-doc&m=158993132907036&w=2 

From `l10n-kf5/ml/messages/`**`applications/dolphin.po`** to `l10n-kf5/ml/`**`dolphin/dolphin.po`**
From `l10n-kf5/ml/messages/`**`kde-workspace/plasma_applet_org.kde.desktopcontainment.po`** to `l10n-kf5/ml/messages/`**`plasma-desktop/plasma_applet_org.kde.desktopcontainment.po`**

I welcome this change, it's much more easier to find files now.

## Applying Changes In Weblate

We can use [`weblate --import-json --update`](https://docs.weblate.org/en/latest/admin/management.html#import-json) to mass update existing components.

- Make JSON of existing components

```
php fetch-components-list.php
```

- Download the [changes spreadsheet](https://share.kde.org/s/RTNwdcZWbreSeNw), convert them to csv

```
soffice --convert-to csv kde_pots_location.ods
```

- Make the new `new-components.json` & a script to move files in out intermediary repo : `new-components-repo-restructure.sh`

```
php make-new-components-list.php
```

- Run newly created `new-components.json` file through [any JSON prettier](https://jsonformatter.org/json-pretty-print) to remove backslashes and make it pretty
- Push changes from Weblate to intermediary repo (if any)
- On intermediary repo, update upstream folder (`svn update`). The structure change will be taken care with VCS
- On intermediary repo, we need to manually update our folder structure (the folder Weblate pulls file from). For this, we made `new-components-repo-restructure.sh` in the step before

```
git checkout -b structure-change
cd l10n-kf5/ml
bash new-components-repo-restructure.sh # should be copied from previous step
```

Remove empty folders with :

```
find . -type d -empty -exec rmdir {} \;
```

We don't need our migration script anymore :

```
rm new-components-repo-restructure.sh
```

Commit :

```
cd ..
git status # do check!
git add .
git commit -am 'Migrating to new folder structure'
git checkout master
git merge --no-ff structure-change
```

Sync :

```
cd / # root of intermediary repo
./copy-to-upstream.sh
svn commit -m 'Update malayalam'

# From upstream to weblate
./copy-from-upstream.sh
git commit -a -m "Sync with KDE Upstream"
git push
```

^ The above steps are a reverse of the steps [mentioned here](https://github.com/subins2000/kde-weblate#syncing-with-kde-upstream).

- Now we need to let Weblate know of the new structure

Copy the `new-components.json` file to server (paste.debian.net). Do a `shasum` to make sure file is correct

Import (🤞) :

```
git pull # kde-weblate repo
weblate runshell < maintenance-scripts/migrate-2020-may-svn-layout-change/weblate-update.py
weblate loadpo kde
```

- Restart Weblate

(You may need to rename repo folder. Since all components will be using a single component's repo, you'll only need to change the setting of that component to apply to all).