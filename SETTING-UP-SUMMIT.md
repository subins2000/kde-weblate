Decided to move the localization system to summit to make things more easier. By using summit, there will be only one repo to manage, and running summit scripts will 

# Initial doc reading

Following [this](https://techbase.kde.org/Localization/Workflows/PO_Summit#Translating_in_Summit)

Summit repo example : https://websvn.kde.org/trunk/l10n-support/fr/summit

* So, scripty doesn't do auto tasks in summit, It's the LANG coordinator who does the merge from templates to summit (merge process) and later, scatter translations from summit (scatter process)

## Repo making

- Setup `$KDEREPO` (the summit repo) :

```
export KDEREPO=$(realpath .)
```

Then, make the repo :

```
cd $KDEREPO
svn co --depth=empty svn+ssh://svn@svn.kde.org/home/kde .
svn up --depth=empty branches branches/stable branches/stable/l10n-kf5 branches/stable/l10n-kf5-plasma-lts
svn up --depth=empty trunk trunk/l10n-support trunk/l10n-kf5 branches/stable/l10n-kf5-plasma-lts
svn up branches/stable/l10n-kf5/{scripts,templates,ml}
svn up branches/stable/l10n-kf5-plasma-lts/{scripts,templates,ml}
svn up trunk/l10n-kf5/{scripts,templates,ml}
svn up trunk/l10n-support/{scripts,templates,ml}
git clone git@invent.kde.org:sdk/pology.git trunk/l10n-support/pology
cd trunk/l10n-support/pology && git pull && cd -
```

- Set PATH :

```
export PATH=$KDEREPO/trunk/l10n-support/pology/bin:$PATH
```

- Intialize summit

You may want to edit `scripts/messages.summit` to configure which branches to use in summit (I avoided plasma5lts & kde4)

```
cd "$KDEREPO/trunk/l10n-support"
posummit scripts/messages.summit ml gather --create --force
posummit scripts/messages.summit ml merge
```

Commit `$KDEREPO/trunk/l10n-support/ml` :

```
svn commit -m 'Init summit for malayalam'
```

## Summit Next Steps

* **NEVER DO `svn commit` on $KDEREPO**, because you might have modified `$KDEREPO/trunk/l10n-support/scripts/messages.summit` which doesn't need to be upstreamed. If an `svn up` fails conflicting on this file, do necessary to keep your settings.

Periodically do these :

- To make summit repo up-to-date

```
cd $KDEREPO
svn up
cd $KDEREPO/trunk/l10n-support
posummit scripts/messages.summit ml merge
svn commit $KDEREPO/trunk/l10n-support/ml
```

- Localization happens in this time period

- To scatter the summit, i.e. fill out POs in stable and trunk branch from the summit POs, the coordinator periodically executes and commit :

```
posummit $KDEREPO/trunk/l10n-support/scripts/messages.summit $KDEREPO/trunk/l10n-support/ml scatter
svn commit $KDEREPO/trunk/l10n-support/ml $KDEREPO/branches/stable/ $KDEREPO/trunk/l10n-kf5/ml
```

There is **no fixed schedule** for when merging and scattering should be done. Of course, it must necessarily be done before the next release is tagged, and in between it is useful to scatter for runtime testing, or to have translation statistics by branches on l10n.kde.org up to date.