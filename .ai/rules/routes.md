---
paths:
  - routes/web.php
---

# Routes

## Trash route ordering before notes resource
Trash routes (notes/trash GET, notes/{note}/restore POST, notes/{note}/force-delete DELETE) must be registered BEFORE Route::resource('notes') so 'trash' isn't swallowed by the {note} catch-all. Restore/force-delete bind with ->withTrashed() so soft-deleted notes resolve. Restore and forceDelete are owner-only (NotePolicy).
