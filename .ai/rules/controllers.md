---
paths:
  - app/Http/Controllers/FolderController.php
  - app/Http/Controllers/NoteController.php
---

# Controllers

## Folder access assignments payload shape
Folder access is managed only via the folder's edit page (folders/{folder}/edit). Assignments are submitted as arrays: group_assignments[<groupId>][group_id|role_id] and user_assignments[<userId>][user_id|role_id]; the controller filters incomplete checkboxes and never stores the owner as an explicit user assignment. Deleting a folder unassigns its notes (folder_id => null) rather than deleting them.

## Note collaborator sync payload shape
Collaborator sharing per note is managed on notes/{note}/share (GET) and synced via PUT notes/{note}/collaborators. Payload shape: collaborators[<userId>][user_id|role_id]; owner row is never stored in note_user (filtered out in sync). Only the owner or an 'admin' collaborator can manage collaborators (NotePolicy::manageCollaborators).
