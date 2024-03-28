# Installation

In order to proceed with the installation of LinguaCafe, it's recommended you've the following programs already installed in your computer:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/), a containerization software to make LinguaCafe into a single container that can be started, stopped, and re-created.
- [Git](https://git-scm.com/), a version manager system. This is most likely already installed in your machine. If it's not, you can use a package manager such as Homebrew if you're using MacOS or your distribution's packager manager if you're using Linux.

Install them first, and then follow the steps below for your operating system. 
## Linux/MacOS

**Step 1:** Clone the LinguaCafe's repository and change into it.

```
git clone -b deploy https://github.com/simjanos-dev/LinguaCafe.git linguacafe && cd linguacafe
```

**Step 2:** If you want to change the default MySQL database and user, you can create a `.env` file and add these lines to it before starting your servers for the first time.

```
DB_DATABASE="linguacafe"
DB_USERNAME="linguacafe"
DB_PASSWORD="linguacafe"
```

**Step 3:** Change the permission for the entire directory, and then proceed to build and start the LinguaCafe service.

> [!WARNING]  
> Make sure Docker Desktop is already running before running the following command.

```
chmod -R 777 ./ && docker compose up -d
```

**Step 4:** Your server now should be running and accessible on http://localhost:9191.  Now open your web browser to `http://localhost:9191`.

## Windows

**Step 1:** Clone the LinguaCafe's repository and change into it.
```
git clone -b deploy https://github.com/simjanos-dev/LinguaCafe.git linguacafe && cd linguacafe
```

**Step 2:** If you want to change the default MySQL database and user, you can create a `.env` file and add these lines to it before starting your servers for the first time.

```
DB_DATABASE="linguacafe"
DB_USERNAME="linguacafe"
DB_PASSWORD="linguacafe"
````

**Step 3:** Change the permission for the entire directory, and then proceed to build and start the LinguaCafe service.

> [!WARNING]  
> Make sure Docker Desktop is already running before running the following command.

```
docker compose up -d
```

**Step 4:** Your server now should be running and accessible on http://localhost:9191.  Now open your web browser to `http://localhost:9191`.

### Script-based Installation

> [!IMPORTANT]  
> The script is a `.bat` file, and Windows defender will warn you about it being potentially a malware.

You can automate the installation and the LinguaCafe service startup by downloading and running the [Windows's LinguaCafe Installation script](/install_linguacafe.bat). 
