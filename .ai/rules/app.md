---
paths:
  - 'app/**'
---

# App

## Folder-level note visibility via FolderAccessService
Note visibility is resolved via App\Services\FolderAccessService: a user's effective role on a folder comes from owner (admin), folder_user assignments, or folder_group assignments via their groups, with parent-folder inheritance. Notes index lists notes owned by the user OR filed in accessible folder ids. NotePolicy then enforces view (viewer+), update (editor+), delete (admin+) per folder role, or collaborator role from note_user. Public notes (is_public) are readable by guests via the /public/notes/{note} route only; private notes 404 for guests.
