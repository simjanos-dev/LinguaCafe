<template>
    <div id="vocabulary-search-box">
        <v-text-field 
            label="Dictionary search"
            class="mt-2 mb-3"
            filled
            dense
            rounded
            width="100%"
            hide-details
            v-model="searchField"
            @change="makeSearchRequest"
            @keydown.stop=";"
        ></v-text-field>

        <!-- Search results -->
        <div id="search-results" class="border rounded-lg">
            <div class="search-result jmdict" v-for="(searchResult, searchresultIndex) in searchResults" :key="searchresultIndex">
                <!-- Regular record -->
                <template v-if="searchResult.dictionary !== 'JMDict'">
                    <div v-for="(record, recordIndex) in searchResult.records" :key="recordIndex">
                        <div class="search-result-title rounded px-2" :style="{'background-color': searchResult.color}">{{ record.word }} <div class="dictionary"> {{ searchResult.dictionary}} </div></div>
                        <div 
                            v-for="(definition, definitionIndex) in record.definitions" 
                            :key="definitionIndex" 
                            class="search-result-definition rounded"
                            @click="addDefinitionToInput(definition)"
                        >
                            {{ definition }} <v-icon>mdi-plus</v-icon>
                        </div>
                    </div>
                </template>

                <!-- JMDict record -->
                <template v-if="searchResult.dictionary == 'JMDict'">
                    <div v-for="(record, recordIndex) in searchResult.records" :key="recordIndex">
                        <div class="search-result-title rounded px-2" :style="{'background-color': searchResult.color}">{{ record.word }} <div class="dictionary"> {{ searchResult.dictionary}} </div></div>
                        <div class="search-result-definition rounded" v-for="(definition, definitionIndex) in record.definitions" :key="definitionIndex" @click="addDefinitionToInput(definition)">
                            {{ definition }} <v-icon>mdi-plus</v-icon>
                        </div>
                    
                        <template v-if="record.otherForms.length">
                            <div class="vocab-box-subheader">Other forms:</div>
                            <div class="d-flex flex-wrap">
                                <div v-for="(form, formIndex) in record.otherForms" :key="formIndex">
                                    {{ form }}<span class="mr-2" v-if="formIndex < record.otherForms.length - 1">, </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            language: String,
            _searchTerm: String
        },
        data: function() {
            return {
                searchField: this.$props._searchTerm,
                searchResults: []
            };
        },
        mounted: function() {
            this.makeSearchRequest();
        },
        methods: {
            addDefinitionToInput(definition) {
                this.$emit('addDefinitionToInput', definition);
            },
            makeSearchRequest() {
                this.searchResults = [];
                if (this.searchField == '') {
                    return;
                }

                axios.post('/dictionary/search', {
                    language: this.$props.language,
                    term: this.searchField
                }).then((response) => {
                    this.processVocabularySearchResults(response.data);
                });
            },
            processVocabularySearchResults(data) {
                this.searchResults = [];

                for (var dictionaryIndex = 0; dictionaryIndex < data.length; dictionaryIndex++) {
                    if (data[dictionaryIndex].name == 'JMDict') {
                        let searchResult = {
                            dictionary: data[dictionaryIndex].name,
                            color: data[dictionaryIndex].color,
                            records: []
                        };

                        for (var jmdictIndex = 0; jmdictIndex < data[dictionaryIndex].jmdictRecords.length; jmdictIndex++) {
                            var jmdictRecord = data[dictionaryIndex].jmdictRecords[jmdictIndex];
                            
                            searchResult.records.push({
                                word: jmdictRecord.words.length ? jmdictRecord.words[0] : '',
                                otherForms: data[dictionaryIndex].jmdictRecords[jmdictIndex].words,
                                definitions: data[dictionaryIndex].jmdictRecords[jmdictIndex].definitions,
                            });                            
                        }

                        this.searchResults.push(searchResult);
                    } else {
                        let searchResult = {
                            dictionary: data[dictionaryIndex].name,
                            color: data[dictionaryIndex].color,
                            records: []
                        };

                        for (var recordIndex = 0; recordIndex < data[dictionaryIndex].records.length; recordIndex++) {
                            searchResult.records.push({
                                word: data[dictionaryIndex].records[recordIndex].word,
                                definitions: data[dictionaryIndex].records[recordIndex].definitions,
                            });                            
                        }

                        this.searchResults.push(searchResult);
                    }
                }
            }
        }
    }
</script>
