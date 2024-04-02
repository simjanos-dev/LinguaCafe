## Contributing
LinguaCafe used to be my personal project, but it got much more popular than I expected after I published it, and serveral people contributed to it since. I've created this guideline for anyone who wants to contribute. This is my first open-source project, and I have no experience with working on open-source software, so please feel free to give any feedback on it.

#### Bug reports and small improvements
I appreciate any bug report, it is important to identify problems with LinguaCafe, so it can be improved. Please feel free to create a GitHub issue for any bug, this includes small problems on the UI as well.

#### Feature requests
In general I'm happy to add most feature requests to the list, but I would also like to keep the scope of LinguaCafe similar to when it's started. Currently I would like to avoid adding a few things:
- Most large AI tools.
- Any built-in video features.
- Niche features that only a small percent of the users would use and dificult to maintain.


#### No pull requests tag on issues 
I've created a **no pull requests** tag for GitHub issues that I would ike to work on myself, please do not send pull requests for them. These are mostly large and not yet designed features like "Manga reader", bugs that require changing significant parts of the software, and issues that I would like to work on to learn software development.

#### Pull requests
If you made a bug fix, or a changed something small that is objectively an improvement like typos or small fixes, feel free to create a PR. If you would like to work on a larger part of the software, please comment on, or create an issue before so it can be discussed.

#### Tests
Currently I don't use tests neither for Javascript, Python or PHP. I am considering adding some tests in the future, but until I figure out what kinds of tests I would like to add to LinguaCafe, please do not write any for PR-s.

#### Code style
I do not use any strict formatting style for the code, please just follow the style of the recent commits and the rest of the code base. There are some old styles that I try to update when I modify parts of the code, mostly just cutting all Laravel SQL queries into separate rows. 

#### Adding a new language
The code must be modified at several points to add new languages, I would recommend just making a feature request for it. There is an ongoing issue (#3) for adding languages. If you would like to try to add a language yourself, you can take a look at the latest commit that added support for new languages.

#### Documentation for devs
I'm planning on adding some basic dev documentation for the code, but I am busy with a lot of things, so it will take a long time.

#### Codebase
LinguaCafe is written with Laravel, Vue and Vuetify. My code is not great, I have an ongoing issue (#103) for improving and refactoring the back-end code to make it more readable and up to standards. Front-end is stuck on Vue 2 currently. I will rewrite it with the latest Vue in the future, but it will take a lot of time. Until that I will be working on separating complex logic into a separate service class files, and possibly breaking large components down into smaller ones.

#### Starting point and basic structure

**Servers**
LinguaCafe has two servers:
- The "webserver" container for Laravel.
- The "python-service" container for a Python microservice, because most language tools are only available in Python. This is used for text parsing and some text imports functions.

**Front-end**
Vue is broken down into separate files for different pages and dialogs. Pages are separated into different directories, which contain separate files for multiple tabs, dialogs and sub components. You can find them in the `linguacafe/resources/js/components` directory. This is a good starting point if you want to find something in the code starting from the UI.


**Back-end**
Back-end follows the standard Laravel structure. You can take a look at the url routes: `linguacafe/routes/web.php` or controllers: `linguacafe/app/Http/Controllers` to find a starting point.

#### User manual
We have a work in progress user manual, it is going through some changes (#187) currently. It is written with Markdown, and will be accessible as a GitHub Wiki online. It will also be stored inside the software, and it will be displayed with the `vue-showdown` library. 

Contributions to the manual are highly appreciated. My english is not my native language, and I don't phrase things the best way. Feel free to change small things, add new sections or rephrase anything I wrote. However if you would like to change a larger part of it that someone else wrote, please open a GitHub issue before you do so it can be discussed. 


#### Branches
- main: Only gets updates when a new version is released. Do not open PR into this branch, except if it's an important bugfix that must be released before the next version. 
- dev: The branch I use for development. Please open every PR into this branch (except ones that must go into deploy branch, or to the main as a hotfix).
- deploy: A branch that contains the docker-compose.yml file, a mostly empty default folder structure and some default files.
- feature/feature-name: these will be merged into dev.

If there are other branches, they are not used anymore. Dev branch will be merged into main when a new version update is ready.

#### Developer environment
Follow these steps to setup your developer environment:
1. Run this command to clone linguacafe into a dev folder: 
```
git clone -b dev https://github.com/simjanos-dev/LinguaCafe.git linguacafe-dev && cd linguacafe-dev
```

2. Run this command to set up the developer docker container: 
```
chmod -R 777 ./ && docker compose -f ./docker-compose-dev.yml up -d
```

3. Run this command to start the localhost server: 
```
docker exec -ti linguacafe-webserver-dev npm run watch-poll
```

You must not use the same folder for both the production and development versions of linguacafe.

You can now reach your dev server on localhost:3000, it will auto reload when you save a .vue or .php file. 

The developer environment does not copy the contents of the linguacafe folder into the docker image, instead it mounts the whole folder, so it is accessible for both the docker container and the developer.

#### Thank you!
Thank you for everyone who contributes to LinguaCafe in any way. I really appreciate it!