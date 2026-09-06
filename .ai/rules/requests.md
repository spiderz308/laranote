---
paths:
  - 'app/Http/Requests/*FolderRequest.php'
  - 'app/Http/Requests/*NoteRequest.php'
---

# Requests

## Server-side folder nesting guard
Folder parent assignment relies on the ValidParentFolder validation rule, not just the UI's availableParents list. It rejects nesting a folder inside itself or any descendant (availableParents already hides those in the form), and requires the parent be accessible (viewer+) to the current user. StoreFolderRequest uses forUpdate:false, UpdateFolderRequest passes forUpdate:true plus the route-bound folder.

## Note folder_id access guard
ValidNoteFolder guards the folder_id on note create/update: a user may only file a note into a folder where they hold at least 'editor' access (FolderAccessService::can). This mirrors ValidParentFolder for folders and prevents planting notes into folders the user cannot access.
