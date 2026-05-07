<?php
//
//libraries/auth.php - Authentication library for Simeck Entertainment's Dropbox as well as session helpers.
//
//provides:
// auth_require() - Middleware to require authentication on a page. Redirects to login.php if not authenticated.
// auth_user() - Returns the currently authenticated user array, or null if not authenticated.
// auth_login_artist - Attempt to auth against the artists table. Returns user array on success, false on failure.
// auth_login_client - Attempt to auth against the clients table. Returns user array on success, false on failure.
// auth_logout() - Logs the user out by clearing the session.
// csrf_token() - Get or Generate a CSRF token for forms.
// csrf_validate($token) - Validate a CSRF token from a form submission.
// ensure_artist_dropbox() - Ensures that artist dropboxes exist on the filesystem, creating them if necessary.
// ensure_client_upload_folders() - Ensures that client upload folders exist on the filesystem, creating them if necessary.
//

?>