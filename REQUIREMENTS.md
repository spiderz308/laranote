# Comprehensive note-taking app
Using the Laravel toolset

1. User Authentication: 
    - Use Laravel's built-in authentication system to handle user registration and login.
    - Implement password hashing and salting to store hashed passwords in the database.
    - Use Laravel's middleware system to authenticate users before allowing access to protected routes.
    - Implement a forgot password feature using Laravel's built-in functionality.
    - add user roles and group  for visibility of notes, like admin user can see all, or employees group can see only notes in employee folder. So a user can be assigned to multiple groups and the groups or users get assigned to the note folder structure.  visibility is then on folder level not per note.
2. Note Creation: 
    - Use a database table to store notes, with columns for note title, content, and user ID.
    - Implement a form to allow users to create new notes, with validation to ensure that title and content are not empty.
    - Use Laravel's Eloquent ORM to interact with the database and store notes.
    - Implement a feature to allow users to create new notes with tags and categories.
3. Note Editing: 
    - Implement a feature to allow users to edit note titles and content.
    - Use Laravel's Eloquent ORM to update notes in the database.
    - Implement a feature to allow users to add tags and categories to notes.
4. Note Deletion: 
    - Implement a feature to allow users to delete notes.
    - Use Laravel's Eloquent ORM to delete notes from the database.
    - Implement a feature to allow users to delete tags and categories.
    - Add a role for the users that can delete note (add/edit/delete roles?)
5. Note Organization: 
    - Use a database table to store note tags and categories.
    - Implement a feature to allow users to create new tags and categories.
    - Use Laravel's Eloquent ORM to interact with the database and store note tags and categories.
    - Implement a feature to allow users to assign tags and categories to notes.
    - the folder structure i want to store in the db along with the notes. not using a physical file path and files for the notes.
6. Search Functionality: 
    - Implement a feature to allow users to search for notes by keyword or title.
    - Use Laravel's Eloquent ORM to interact with the database and store search results.
    - Implement a feature to allow users to filter search results by tags and categories.
    - or search by tag. add extra section for filtering and expand on that
7. Tagging: 
    - Use a database table to store note tags.
    - Implement a feature to allow users to create new tags.
    - Use Laravel's Eloquent ORM to interact with the database and store note tags.
    - Implement a feature to allow users to assign tags to notes.
    - multiple tags are allowed per note
8. Folder Structure: 
    - Use a database table to store note folders.
    - Implement a feature to allow users to create new folders.
    - Use Laravel's Eloquent ORM to interact with the database and store note folders.
    - Implement a feature to allow users to assign folders to notes.
9. Note Sharing: 
    - Implement a feature to allow users to share notes with others.
    - Use Laravel's Eloquent ORM to interact with the database and store shared notes.
    - Implement a feature to allow users to unshare notes.
    - note will have to be public to be seen by non-authenticated users
10. Collaboration: 
    - Implement a feature to allow users to collaborate on notes.
    - Use Laravel's Eloquent ORM to interact with the database and store collaborative notes.
    - Implement a feature to allow users to assign roles to collaborators.
11. Version Control: 
    - Use a database table to store note versions.
    - Implement a feature to allow users to create new versions of notes.
    - Use Laravel's Eloquent ORM to interact with the database and store note versions.
    - Implement a feature to allow users to revert to previous versions of notes.
12. Accessibility: 
    - Implement a feature to allow users to access notes on various devices.
    - Use Laravel's built-in functionality to implement responsive design.
    - Implement a feature to allow users to access notes on multiple platforms (e.g. web, mobile).
13. Themes
    - add extra section for themes, at least light and dark themes.



# Process:

* Plan
    - Outline project goals and scope
* Make tasks
    - Break down the plan into actionable steps
* Make the code
    - Develop the software based on the tasks
* Review
    - Evaluate code and ensure quality
* Test
    - Verify functionality and identify bugs
* Done
    - Finalize and prepare for deployment
* Document
* Debug

# Primary Agent Orchestraror
* Primary Agent - Project Manager
    * Analyise Requirements
    * Break down Tasks
    * Coordinate Sub-agents
    * Integrate Results
* Sub-agent Specialists
    * Document Agent
    * Code Review Agent
    * Testing Agent
    * Simple coding Agent
* Shared Resources
    * Architecture Docs
      @docs/architecture
      Project Structure
    * Workflow docs
      @workflows/development
      Process Guidelines
    * File Operations
      @tools/file-operations
      Available Tools

