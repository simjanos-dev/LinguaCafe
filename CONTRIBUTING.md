## Contributing
I've started working on and shared LinguaCafe as a personal project, and used a different project management tool, but some people have asked me how they can contribute to it, so I've decided to migrate all my tasks to GitHub, where it's accessible for everyone. 

I would like to keep making decisions on the general direction of LinguaCafe and implement most of the large features myself, because I intend to keep using it for decades and I want to understand how all the code works in every part of it. But In general I appreciate any contribution, and I'm happy to add most feature requests to the list. I've never worked on an open-source project with other people, and I do not know how it supposed to be done properly. I've made these simple guidelines, if I missed something or you have any input on them, then please create an Issue, I would love some feedback on it.

## Tags
I've decided to use these tags for the Issues:
- **bug**: Any functional issue with the software.
- **feature**: A new feature to be implemented.
- **code changes**: Code changes that do not have a functional impact on the code from the end-user point of view, but improve it in some way: refactoring, fixing typos, optimization, formatting or documentation.
- **no tags**: For anything else.
- **no pull requests**: Issues that I want to work on personally. Please do not send pull requests for them. These are mostly large and not yet designed features like "Manga reader".

## Issues created by me
I'll add every **feature** and **code changes** that I plan to implement and any **bug** I find as an Issue.

## New bug
If you have found a **bug**, you can create an Issue, or if you want to fix it yourself you can send a pull request. 

## Feature or code change request
If you have a **feature** or **code change** request, please create an Issue. Only create pull requests without previous discussion for **bugs**, obvious **code change** improvements and smaller **code changes** like typos.  


I hope these are rasonable requests, I'll read, comment and tag every Issue. Since I work actively on the project, I will figure out a way to mark the Issues I'm currently working on..

## Adding a new language
If you want to add a new language to the "Experimental" languages that Spacy supports, please feel free create a pull request. There is a series of commits documenting the process of adding a new language on 2024 January 09 named "Adding language support 1-7" (4074987e266efb620b4c3a490f03ad0d1575697c). 

If you want to add a new language that needs a new tokenizer, please create an Issue.

## Documentation and code
I wasn't expecting other people to contribute, so I never written a documentation, I will try to do it soon. There are a lot of old code that needs to be updated(creating service classes, putting some code into models from the controllers, updating to Laravel 11), but I hope I made it clean and readable enough for other people to work on.

## Branches
Please make pull requests only to the "dev" branch. I will merge them and they will be added to the main branch later on the next version release.

Thank you for anyone, who want to contribute!