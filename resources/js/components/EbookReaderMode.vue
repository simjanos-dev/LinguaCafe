<template>
    <li class="nav-item ebook-mode-button">
        <a v-if="ebookReaderMode == null" class="dropdown-item" @click="refresh">
            <i class="fa fa-tablet-alt"></i>&nbsp;&nbsp;E-ink mode
        </a>
        <a v-if="ebookReaderMode !== null" class="dropdown-item" @click="refresh">
            <i class="fa fa-desktop"></i>&nbsp;&nbsp;Desktop mode
        </a>
    </li>
</template>

<script>
    export default {
        data: function() {
            return {
                ebookReaderMode: this.$cookie.get('ebook-reader-mode'),
            }
        },
        props: {
            
        },
        mounted() {
            
        },
        methods: {
            refresh() {
                if (this.ebookReaderMode === null) {
                    this.$cookie.set('ebook-reader-mode', 'yes', 31);
                    
                } else {
                    this.$cookie.delete('ebook-reader-mode');
                }

                window.location.href = '/';
            }
        }
    }
</script>
