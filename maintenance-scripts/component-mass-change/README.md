## Updating filemask

Folder `l10n-kf5` in intermediary repo was renamed to `pos`. Updating each component's filemask with new path :

```
from weblate.trans.models import Component;
for c in Component.objects.all():
  c.filemask = c.filemask.replace("l10n-kf5/", "pos/")
  c.save()
``` 

This was executed in `weblate shell`.
