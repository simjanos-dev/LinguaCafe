<template>
    <div id="user-manual-backup" class="ma-4 mb-16">
        <div class="subheader d-flex">Backup</div>
        <v-card outlined class="rounded-lg pa-4">
            LinguaCafe stores your data in two directories. The <code>linguacafe/storage</code> directory stores your files, and the <code>linguacafe/database</code> directory 
            stores your database files. Both must be saved to preserve all your LinguaCafe data.

            <div class="mt-5">
                To make a backup of your linguacafe, just simply copy your whole <code>linguacafe</code> directory. On Linux you may need root privileges to copy the database folder, 
                so please make sure that it was successful. Also make sure that the permissions are the same after restoring your data. You can reapply them by using the chmod command from the 
                installation guide.
            </div>

            <div class="mt-5">
                Although copying the whole database folder works, you might also want to make a raw export of your database. This removes dependency on a functioning MySql docker container, and 
                you can have all your database data in a single .sql file.
            </div>

            <div class="mt-5">
                Run this command while your LinguaCafe server is running to export your database. If your database setup was changed manually, change names accordingly:<br>
                <code>
                    docker exec linguacafe-database mysqldump --no-tablespaces -ulinguacafe -plinguacafe linguacafe > ./linguacafe-backup.sql
                </code>
            </div>

            <div class="mt-5">
                You can import it back with the following command:<br>
                <code>
                    docker exec -i linguacafe-database mysql -ulinguacafe -plinguacafe linguacafe < ./linguacafe-backup.sql
                </code>
            </div>

            <div class="mt-5">
                I highly recommend making regular backups, especially before upding to a newer version. Since linguacafe is still in active development, there is a higher possibility 
                of introducing a data corrupting bug.
            </div>
        </v-card>
    </div>
</template>

<style scoped>
    #user-manual-backup {
        width: 100%;
        max-width: 1000px;
    }

    #user-manual-backup ul {
    }

    #user-manual-backup ul li {
        list-style: none;
    }
</style>

<script>
    export default {
        data: function() {
            return {
            }
        },
        props: {
        },
        methods: {
        }
    }
</script>
 
