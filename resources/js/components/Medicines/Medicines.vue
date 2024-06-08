<template>
  <v-app>
    <div data-app>
      <div class="card add-product card--content-center">
        <div class="card__wrapper">
        <v-container fluid>
          <v-row>
            <v-col>
              <h1>Medicine List</h1>
              
              <v-data-table-server
                density="compact"
                v-model:page="currentPage"
                v-model="selectedRows"
                :items-length="totalItems"
                v-model:items-per-page="itemsPerPage"
                :loading="loading"
                :headers="headers"
                :items="medicines"
                class="elevation-0 sticky-table no-wrap-table"
                show-select return-object
              >
                <template v-slot:top>
                  <v-toolbar flat>
                    <v-toolbar-title>Medicines</v-toolbar-title>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-spacer></v-spacer>
                    <v-text-field
                      v-model="search"
                      append-icon="mdi-magnify"
                      label="Search"
                      single-line
                      hide-details
                    ></v-text-field>
                  </v-toolbar>
                </template>
                <template v-slot:item.name="{ item }">
                  <strong>{{ item.name }}</strong>
                </template>
                <template v-slot:item.code="{ item }">
                  <strong>{{ item.code }}</strong>
                </template>
              </v-data-table-server>
            </v-col>
          </v-row>
        </v-container>
      </div>
      </div>
    </div>
  </v-app>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      medicines: [],
      totalItems: 0,
      options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: [],
        sortDesc: [],
      },
      search: '',
      loading: false,
      headers: [
        { text: 'Name', value: 'name' },
        { text: 'Code', value: 'code' },
        { text: 'Dosage', value: 'dosage1' },
        { text: 'Unit Dosage', value: 'unit_dosage1' },
        { text: 'Shape', value: 'shape' },
        { text: 'Presentation', value: 'presentation' },
        { text: 'PPV', value: 'ppv' },
        { text: 'Price', value: 'price_br' },
        { text: 'Refund Rate', value: 'refund_rate' },
      ],
    };
  },
  watch: {
    options: {
      handler() {
        this.getData();
      },
      deep: true,
    },
    search() {
      this.options.page = 1;
      this.getData();
    },
  },
  methods: {
    getData() {
      this.loading = true;
      const { page, itemsPerPage, sortBy, sortDesc } = this.options;

      axios
        .get('/medicines/get-data', {
          params: {
            page,
            perPage: itemsPerPage,
            sortBy: sortBy.length ? sortBy[0] : null,
            sortDesc: sortDesc.length ? sortDesc[0] : null,
            search: this.search,
          },
        })
        .then(response => {
          this.medicines = response.data.medicines.data;
          this.totalItems = response.data.total_appointments;

          console.log('medicines: ',this.medicines)
          this.loading = false;
        })
        .catch(error => {
          console.error("There was an error fetching the medicines:", error);
          this.loading = false;
        });
    },
  },
  mounted() {
    this.getData();
    
  },
};
</script>

<style scoped>
</style>
