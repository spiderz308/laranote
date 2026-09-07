https://www.deployhq.com/blog/deploy-laravel-to-cpanel-shared-hosting

//clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear



composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache

The output — vendor/, the built public/build/ assets, cached config — becomes the artifact.

4. Handle Laravel's public-directory quirk. On shared hosting the web root is usually public_html, not your app's public/. Map the deploy so the built public/ contents land in public_html, and keep the framework files above the web root. This is a mapping decision, not a code change.

5. Deploy. Push to your branch. The build runs off-host, the finished files transfer to public_html, and the site updates — with a deploy log for each run, so a failed transfer tells you what happened instead of leaving you guessing.

If that sequence is where you're stuck right now, connecting a repository and a shared-hosting target is the fastest way to see it work against your own setup.




Unrestricted
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned


https://medium.com/@houdaifaboucenna/deploying-laravel-on-shared-hosting-no-ssh-required-34b409efc7ae





