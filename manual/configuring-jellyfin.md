# Jellyfin
## Jellyfin configuration

You can use the network configuration from this example to connect Jellyfin's network with LinguaCafe. There are probably multiple ways to do it, the only requirement is that `linguacafe-webserver` should be able to reach Jellyfin's server to make API requests.

```
version: '3.5'
networks:
    linguacafe_linguacafe:
        external: true

services:
    jellyfin:
        image: jellyfin/jellyfin
        container_name: jellyfin
        user: 1000:1000
        volumes:
            - /path/to/config:/config
            - /path/to/cache:/cache
            - /path/to/media:/media:ro
        restart: 'unless-stopped'
        ports:
            - 8096:8096
        networks:
            - linguacafe_linguacafe
```

You must name your subtitle files in a way that Jellyfin will recognize as languages. These worked for me: 

```
Series Name - S01E01.ja.ass  
Series Name - S01E01.de.ass  
Movie name.es.ass  
```  

Language codes for subtitle filenames that Jellyfin recognizes:

| Language | Language Code |
| :--- | ---- |
| Chinese | `zh` |
| Czech | `cs` |
| Dutch | `nl` |
| Finnish | `fi` |
| French | `fr` |
| German | `de` |
| Italian | `it` |
| Japanese | `ja` |
| Korean | `ko` |
| Norwegian | `no` |
| Russian | `ru` |
| Spanish | `es` |
| Swedish | `sv` |
| Ukrainian | `uk` |
| Welsh | `cy` |

See [Jellyfin external file naming](https://jellyfin.org/docs/general/server/media/external-files/).

## Jellyfin API usage

1. Create an API key in Jellyfin. You can do this on the **Dashboard** > **API Keys** menu.
2. Set the created API key in LinguaCafe on to the **Admin** > **API** menu.
3. Set the Jellyfin host in LinguaCafe on to the **Admin** > **API** menu. If you used the pre-written configs, it should be the default http://jellyfin:8096.
4. Save the settings.

Now you can import subtitles from Jellyfin.
## Jellyfin troubleshooting

Possible error codes in browser console while importing from Jellyfin:

<details>
<summary><b>Error: unsupported language code: spa</b></summary>

This means that Jellyfin recognized the language of the subtitle, but it is not supported by LinguaCafe yet. If you find one of these, please open a GitHub Issue, this should be fixed. 

</details>

<details>
<summary><b>Error: unsupported language code: unrecognized by jellyfin: japaaaneseee</b></summary>

This means that Jellyfin did not recognize `japaaaneseee` as a language, and it can only be fixed by renaming the file following Jellyfin's naming conventions. 

If you have file naming issues and renamed a file, make sure you refresh metadata in Jellyfin before reloading LinguaCafe.

</details>






