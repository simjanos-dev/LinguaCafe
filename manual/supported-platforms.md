# Supported Platforms

LinguaCafe is supported in the following platforms:
- x64, which includes most desktop computers made in the last decade.
- Macs with Apple silicon are supported, but need to uncomment the line that says `platform: linux/amd64` by removing the "#" near the end of the `docker-compose.yml`file. To do this, you will need to split the chained install command, first clone the repository, then uncomment the line, then run the rest of the commands. You will also need to comment it and uncomment it again for each update to avoid git conflict error.

Other Armv8 devices such as Raspberry Pis 3 and newer are not supported yet.