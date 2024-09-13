# OAuth API with Google Integration

This project demonstrates the integration of Google OAuth with a Laravel application using Laravel Socialite. It includes routes for user authentication and token management.

## Prerequisites

- PHP 8.x
- Laravel 10.x
- Composer
- MySQL or any other database supported by Laravel
- Google OAuth credentials ([Google.com](https://console.cloud.google.com/apis/credentials))
- Postman and web browser 

## Installation and environment

1. **Clone the repository:**

 
   git clone [project-repo](https://github.com/Dev-Tams/OAUTH-API.git)
     ``` bash
     cd oauth-api 
   ```
2. **Install dependecies:**
    ``` bash
     composer install ```

3. **Add the following variables to your `.env`file:**

- GOOGLE_CLIENT_ID= your-google-client-id [Google.com](https://console.cloud.google.com/apis/credentials)
- GOOGLE_CLIENT_SECRET= your-google-client-secret [Google.com](https://console.cloud.google.com/apis/credentials)
- GOOGLE_REDIRECT_URI= http://localhost:8000/auth/google/callback

4. **Run the Migrations**
php artisan migrate


## Routes

### Authentication
-  Redirect to google 
``` GET /auth/google ```

-  Google callback
``` GET /auth/google/callback ```

### User
- Register
```POST /api/register```

- Login
``` POST /api/login ```

- Password reset link
``` POST /api/password/reset-link ```

- Password reset
```POST /api/password/reset ```

