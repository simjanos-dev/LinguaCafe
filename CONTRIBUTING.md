# Contributing

LinguaCafe used to be my personal project, but it got much more popular than I expected after I published it, and several people contributed to it since. I've created this guideline for anyone who wants to contribute. This is my first open-source project, and I have no experience with working on open-source software, so please feel free to give any feedback on it.

## Bug reports and small improvements

I appreciate any bug report, it is important to identify problems with LinguaCafe, so it can be improved. Please feel free to create a GitHub issue for any bug, this includes small problems on the UI as well.

## Feature requests

In general I'm happy to add most feature requests to the list, but I would also like to keep the scope of LinguaCafe similar to when it's started. Currently I would like to avoid adding a few things:

-   Most large AI tools.
-   Any built-in video features.
-   Niche features that only a small percent of the users would use and difficult to maintain.

#### No pull requests tag on issues

I've created a **no pull requests** tag for GitHub issues that I would like to work on myself, please do not send pull requests for them. These are mostly large and not yet designed features like "Manga reader", bugs that require changing significant parts of the software, and issues that I would like to work on to learn software development.

#### Pull requests

If you made a bug fix, or a changed something small that is objectively an improvement like typos or small fixes, feel free to create a PR. If you would like to work on a larger part of the software, please comment on, or create an issue before so it can be discussed.

## Tests

Tests are meant to be run with this command:

```sh
./tests/run_tests.sh --with-language-installs
```

Running the tests will delete everything from the database and uninstall every installable package. After the tests are finished, the test data will remain in the database and can be used for manual testing. The database will contain one user, with "password" set as password.

Running the command without the `--with-language-installs` argument will skip uninstalling and installing language packages. If the language installs are skipped, the following tests will only test the installed languages.


## Styling/formatting/linting
Linguacafe uses several linters to maintain consistent code formatting. At the time of this writing, linting is not applied automatically and should be applied manually before making a PR.

### **PHP**
For PHP LC uses Pint as a linter. You can run it with `./vendor/bin/pint` command, or use the Laravel Pint vscode plugin to autoformat on save. Please also break lines that seem too long, start eloquent queries with `::query()`, and put chained commands in new lines.

```php
$nextChapter = Chapter::query()
    ->where('book_id', '=', $currentChapter->book_id)
    ->where('id', '>', $currentChapter->id)
    ->orderBy('id', 'asc')
    ->first();
```

### **HTML, JS and Vue**
Prettier. 

### **Python**
Ruff

### **My VSCode config**

```json
"[html]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": false
},
"[vue]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
},
"[js]": {
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.formatOnSave": true
},
"[python]": {
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "charliermarsh.ruff"
},
```

## Adding a new language

The code must be modified at several points to add new languages, I would recommend just making a feature request for it. There is an ongoing issue (#3) for adding languages. If you would like to try to add a language yourself, you can take a look at the latest commit that added support for new languages.

## Documentation for devs

I'm planning on adding some basic dev documentation for the code, but I am busy with a lot of things, so it will take a long time.

## Codebase

LinguaCafe is written with Laravel, Vue and Vuetify. My code is not great, I have an ongoing issue (#103) for improving and refactoring the back-end code to make it more readable and up to standards. Front-end is stuck on Vue 2 currently. I will rewrite it with the latest Vue in the future, but it will take a lot of time. Until that I will be working on separating complex logic into a separate service class files, and possibly breaking large components down into smaller ones.

### Starting point and basic structure

**Servers**
LinguaCafe has two servers:

-   The "webserver" container for Laravel.
-   The "python-service" container for a Python microservice, because most language tools are only available in Python. This is used for text parsing and some text imports functions.

**Front-end**
Vue is broken down into separate files for different pages and dialogs. Pages are separated into different directories, which contain separate files for multiple tabs, dialogs and sub components. You can find them in the `linguacafe/resources/js/components` directory. This is a good starting point if you want to find something in the code starting from the UI.

**Back-end**
Back-end follows the standard Laravel structure. You can take a look at the url routes: `linguacafe/routes/web.php` or controllers: `linguacafe/app/Http/Controllers` to find a starting point.

### Branches

-   main: Only gets updates when a new version is released. Do not open PR into this branch, except if it's an important bugfix that must be released before the next version.
-   dev: The branch I use for development. Please open every PR into this branch (except ones that must go into deploy branch, or to the main as a hotfix).
-   deploy: A branch that contains the docker-compose.yml file, a mostly empty default folder structure and some default files.
-   feature/feature-name: these will be merged into dev.

If there are other branches, they are not used anymore. Dev branch will be merged into main when a new version update is ready.


## User manual

We have a work in progress user manual. It is written with Markdown, and it is accessible on the GitHub wiki online, as well as inside the software with the help of `vue-showdown` library.

Contributions to the manual are highly appreciated. English is not my native language, and I don't phrase things the best way. Please feel free to change small things, add new sections or rephrase anything I wrote. However if you would like to change a larger part of it that someone else wrote, please open a GitHub issue before you do so it can be discussed.

**User manual structure**
If you would like to open a PR for the user manual, you can use the dev branch's `/manual` folder. If a PR gets merged, it will be in the software at the next version release, and I will copy it to the GitHub wiki repository manually.

If you would like to create a page in the user manual, create a file in the manual folder `/manual/Filename.md`.

If you would like to create a sub-page inside one of the pages, you can add one by making a line inside the file starting with `# `. For example `# Languages`

**User manual menu naming rules**:
Can only contain lower and uppercase letters, numbers, `,`, `.`, `?` characters and spaces. Don't use the same name for two different pages or sub-pages. If these naming rules are not followed, the sub page scrolling will not work inside LinguaCafe. If you would like more characters to be added, please open a GitHub issue.

## Developer environment

Follow these steps to setup your developer environment.

**Step 1:** Run these commands to clone LinguaCafe into a dev folder:

```sh
git clone -b dev https://github.com/simjanos-dev/LinguaCafe.git linguacafe-dev

cd linguacafe-dev
```

**Step 2:** Run this command to set up the developer docker container:

Linux and regular MacOs:

```sh
docker compose -f ./docker-compose-dev.yml up -d --force-recreate
```

MacOs with Apple silicon processors:

```sh
docker compose -f ./docker-compose-dev-macos.yml up -d --force-recreate
```

Windows:

```sh
docker compose -f ./docker-compose-dev.yml up -d --force-recreate
```

**Step 3:** Run this command to start the localhost server:

```
docker exec -ti linguacafe-webserver-dev npm run watch-poll
```

You must not use the same folder for both the production and development versions of linguacafe.

You can now reach your dev server on localhost:3000, it will auto reload when you save a .vue or .php file.

The developer environment does not copy the contents of the linguacafe folder into the docker image, instead it mounts the whole folder, so it is accessible for both the docker container and the developer.

### Dev Containers

It's not necessary, but if you're developing with VSCode, you might want to setup [Dev Containers](https://code.visualstudio.com/docs/devcontainers/containers).
1. Install the offical [Dev Containers VSCode Extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers).
2. There is an example devcontainer configuration file for both the `webserver` and `python` containers. Copy the example files and change the settings per your preference.
    - Webserver: Copy the `.devcontainer/webserver/devcontainer.json.example` to `.devcontainer/webserver/devcontainer.json`
    - Python: Copy the `.devcontainer/python/devcontainer.json.example` to `.devcontainer/python/devcontainer.json`
3. You may need to restart VSCode at this point, but you should now have several Dev Container commands available in the command pallette.

    <img width="597" height="239" alt="image" src="https://github.com/user-attachments/assets/0a55d6dc-b182-49bc-807e-1d2b37839d24" />
5. If you've already spun up your environment with `docker compose` then you can attach to an existing container with the "Attach to Running Container" command.

    <img width="599" height="173" alt="image" src="https://github.com/user-attachments/assets/b079be94-8a87-46ec-a705-09c7c58a8b6c" />
7. Alternatively, once you've created a Dev Container for the first time, you should be able to run the "Reopen in container" command whenever you've opened the `linguacafe` directory in VSCode.
    
    - This will display a list of containers to reopen.
        <img width="588" height="138" alt="image" src="https://github.com/user-attachments/assets/f598cf55-3e07-4ad8-a623-45cdd43b695e" />
    - For example, both the python service and the Laravel web server.
        <img width="587" height="82" alt="image" src="https://github.com/user-attachments/assets/8652f453-9178-4370-9c1f-7ee589905b54" />
8. If you run into issues after you're up and running, try running the "Rebuild Container" and/or "Reload window". You may also sometimes need to rebuild the container without the cache using the "Rebuild Container Without Cache" command, but that should be a last resort since it will take quite a while to do a full rebuild and install all the spacy models. Rebuilding without cache should only be necessary after some major changes to the Docker files.

### Debugging
LC has 3 major components, the Vue frontend, the Laravel backend, and the Python Bottle microservice. Each requires their own configuration for debugging. As of now debugging is only configured for Laravel and python while using the Dev Containers mentioned above.

#### Python
If you're using the Dev Container configuration in VSCode, then Python debugging should work seamlessly via VSCode as long as you have the [Python Extension](https://marketplace.visualstudio.com/items?itemName=ms-python.python) installed which is included in the default python `devcontainer.json` config. 

#### Laravel
The Laravel backend can be be debugged via XDebug. See `socker/xdebug.ini`. Currently it is only configured to function within the Dev Container. It should work seamlessly via VSCode as long as the [PHP Debug Extension](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) is installed which is included in the default webserver `devcontainer.json` config.

### Laravel Horizon

Laravel Horizon is the process that runs Laravel jobs. For code changes to take effect in jobs, you will have to restart Horizon. Supervisor makes sure that the Horizon process is always running, so you can restart it with this command:

```sh
docker exec -ti linguacafe-webserver-dev php artisan horizon:terminate
```

### Updating the development environment

If there is a change in docker-compose yml file, or in one of the dockerfiles, you can use these commands to update your docker image in your developer environment.

Windows, Linux and regular MacOs:

```sh
docker compose -f ./docker-compose-dev.yml build --no-cache
```

MacOs with Apple silicon processors

```sh
docker compose -f ./docker-compose-dev-macos.yml build --no-cache
```

## Testing http requests

I've made a simple tool to test http requests from the context of the logged in user. You can reach it at the `/dev` url.

## Thank you!

Thank you for everyone who contributes to LinguaCafe in any way. I really appreciate it!
