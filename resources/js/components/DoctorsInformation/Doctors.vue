<template>
  <v-container>
    <v-row>
      <v-col>
        <h1>Liste d'informations sur le médecin</h1>
        <v-text-field
          v-model="search"
          label="Search"
          @input="handleSearch"
        ></v-text-field>
        <v-list>
          <v-list-item
            v-for="doctor in doctors"
            :key="doctor.id"
            @click="selectDoctor(doctor)"
          >
            <v-list-item-content>
              <v-list-item-title>{{ formatDoctorLabel(doctor) }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-pagination
          v-if="totalPages > 1"
          v-model="page"
          :length="totalPages"
          @input="handlePageChange"
        ></v-pagination>

        <v-dialog v-model="showModal" max-width="600px">
          <v-card>
            <v-card-title>{{ selectedDoctor.business_name }}</v-card-title>
            <v-card-text>
              <p><strong>URL de Google Place :</strong> <a :href="selectedDoctor.google_place_url" target="_blank">{{ selectedDoctor.google_place_url }}</a></p>
              <p><strong>Téléphone:</strong> {{ selectedDoctor.business_phone }}</p>
              <p><strong>Taper:</strong> {{ selectedDoctor.type }}</p>
              <p><strong>Sous-types :</strong> {{ selectedDoctor.sub_types }}</p>
              <p><strong>Catégorie:</strong> {{ selectedDoctor.category }}</p>
              <p><strong>Adresse:</strong> {{ selectedDoctor.full_address }}</p>
              <p><strong>Rue:</strong> {{ selectedDoctor.street }}</p>
              <p><strong>Ville:</strong> {{ selectedDoctor.city }}</p>
              <p><strong>Pays:</strong> {{ selectedDoctor.country }}</p>
              <p><strong>Latitude:</strong> {{ selectedDoctor.latitude }}</p>
              <p><strong>Longitude:</strong> {{ selectedDoctor.longitude }}</p>
            </v-card-text>
            <v-card-actions>
              <v-btn color="primary" @click="showModal = false">Close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      doctors: [],
      page: 1,
      perPage: 10,
      totalPages: 0,
      search: '',
      showModal: false,
      selectedDoctor: {},
    };
  },
  watch: {
    page(newPage) {
      this.getData();
    }
  },
  methods: {
    getData() {
      axios
        .get('/doctors-information/get-data', {
          params: {
            page: this.page,
            perPage: this.perPage,
            search: this.search,
          },
        })
        .then(response => {
          this.doctors = response.data.data;
          this.totalPages = Math.ceil(response.data.total / this.perPage);
        })
        .catch(error => {
          console.error("There was an error fetching the doctors:", error);
        });
    },
    selectDoctor(doctor) {
      this.selectedDoctor = doctor;
      this.showModal = true;
    },
    formatDoctorLabel(doctor) {
      return [
        doctor.business_name ? doctor.business_name + ', ' : '',
        doctor.business_phone ? doctor.business_phone + ', ' : '',
        doctor.type ? doctor.type + ', ' : '',
        doctor.category ? doctor.category + ', ' : '',
        doctor.city ? doctor.city + ', ' : '',
        doctor.country
      ].join('');
    },
    handlePageChange(newPage) {
      this.page = newPage;
    },
    handleSearch() {
      this.page = 1; // Reset to first page when search query changes
      this.getData();
    }
  },
  mounted() {
    this.getData();
  },
};
</script>

<style scoped>
.v-list-item {
  border-bottom: 1px solid #ccc;
  margin-bottom: 10px;
}
</style>
