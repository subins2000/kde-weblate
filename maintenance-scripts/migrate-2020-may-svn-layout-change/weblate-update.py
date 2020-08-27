import json
from weblate.trans.models import Component

data = json.loads(open('new-components.json', 'r').read())

for item in data:
  id = item['id']
  c = Component.objects.get(id=id)
  c.name = item['name']
  c.slug = item['slug']
  c.filemask = item['filemask']
  c.save()