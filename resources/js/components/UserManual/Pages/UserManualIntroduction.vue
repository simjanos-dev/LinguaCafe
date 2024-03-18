<template>
    <div id="user-manual-introduction" class="ma-4 mb-16">
        <div class="subheader d-flex">LinguaCafe</div>
        <v-card outlined class="rounded-lg pa-4">
            LinguaCafe is a free self-hosted software that helps language learners acquire vocabulary by reading. It provides a 
            set of tools to read, look up, and review unknown words as effortlessly as possible.

            <div class="mt-5">
                It is not designed to teach you a language, rather it is meant to help you acquire words by reading and listening to 
                native material by providing tools that help you understand unknown parts of texts. As a beginner learner it is very
                difficult to start reading your first native text, and work your way through it. However with LinguaCafe, the only thing 
                that separates you from understanding an unknown word is moving your cursor over it, and you can save it to study
                later as simply as clicking on it. 
            </div>

            <div class="mt-5">
                There are many people learning languages with different methods. If your's includes reading, then LinguaCafe can be a great
                choice of tool. If you are unsure where to start, I recommend you researching <b>immersion language learning</b>. 
            </div>

            <div class="mt-5">
                LinguaCafe supports importing text from several sources:
                <ul class="mt-2">
                    <li><v-icon class="mr-2 mb-1">mdi-text-box</v-icon>Plain text</li>
                    <li><v-icon class="mr-2 mb-1">mdi-file-document</v-icon>Text file</li>
                    <li><v-icon class="mr-2 mb-1">mdi-book</v-icon>E-book file</li>
                    <li><v-icon class="mr-2 mb-1">mdi-youtube</v-icon>Youtube subtitle</li>
                    <li><v-icon class="mr-2 mb-1">mdi-subtitles</v-icon>Subtitle files</li>
                    <li><v-icon class="mr-2 mb-1">mdi-movie</v-icon>Jellyfin external subtitle</li>
                    <li><v-icon class="mr-2 mb-1">mdi-web</v-icon>Websites</li>
                </ul>
            </div>

            <div class="mt-5">
                LinguaCafe is still in active development, improving and changing from month to month. Please test it before you start
                actively using it, and make sure it is up to you expectaions. If you have a bug report, feature request, or suggestion, 
                you can reach me on 
                    <a href="https://discord.com/invite/wZYZYrdaeP">
                        <v-icon small>mdi-message-text</v-icon>
                        Discord
                    </a> or 
                    <a href="https://github.com/simjanos-dev/linguacafe">
                        <v-icon small>mdi-github</v-icon>
                        GitHub
                    </a>.
            </div>

        </v-card>
    </div>
</template>

<style scoped>
    #user-manual-introduction {
        width: 100%;
        max-width: 1000px;
    }

    #user-manual-introduction ul {
    }

    #user-manual-introduction ul li {
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
 
