<template>
  <v-container>
    <v-row>
      <v-col>
        <h1>Liste des médicaments</h1>
        <v-text-field
          v-model="search"
          label="Search"
          @input="handleSearch"
        ></v-text-field>
        <v-list>
          <v-list-item
            v-for="medicine in medicines"
            :key="medicine.id"
            @click="selectMedicine(medicine)"
          >
            <v-list-item-content>
              <v-list-item-title>{{ formatMedicineLabel(medicine) }}</v-list-item-title>
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
            <v-card-title>{{ selectedMedicine.name }}</v-card-title>
            <v-card-text>
              <p><strong>Code:</strong> {{ selectedMedicine.code }}</p>
              <p><strong>DCI :</strong> {{ selectedMedicine.dci1 }}</p>
              <p><strong>Dosage:</strong> {{ selectedMedicine.dosage1 }} {{ selectedMedicine.unit_dosage1 }}</p>
              <p><strong>Forme:</strong> {{ selectedMedicine.shape }}</p>
              <p><strong>Présentation:</strong> {{ selectedMedicine.presentation }}</p>
              <p><strong>Télévision à la carte :</strong> {{ selectedMedicine.ppv }}</p>
              <p><strong>Prix:</strong> {{ selectedMedicine.price_br }}</p>
              <p><strong>Taux de remboursement :</strong> {{ selectedMedicine.refund_rate }}</p>
            </v-card-text>
            <v-card-actions>
              <v-btn color="primary" @click="showModal = false">Fermer</v-btn>
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
      medicines: [],
      page: 1,
      perPage: 10,
      totalPages: 0,
      search: '',
      showModal: false,
      selectedMedicine: {},
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
        .get('/medicines/get-data', {
          params: {
            page: this.page,
            perPage: this.perPage,
            search: this.search,
          },
        })
        .then(response => {
          this.medicines = response.data.data;
          this.totalPages = Math.ceil(response.data.total / this.perPage);
        })
        .catch(error => {
          console.error("There was an error fetching the medicines:", error);
        });
    },
    selectMedicine(medicine) {
      this.selectedMedicine = medicine;
      this.showModal = true;
    },
    formatMedicineLabel(medicine) {
      return [
        medicine.name ? medicine.name + ', ' : '',
        medicine.dosage1 ? medicine.dosage1 + ' ' : '',
        medicine.unit_dosage1 ? medicine.unit_dosage1 + ', ' : '',
        medicine.shape ? medicine.shape + ', ' : '',
        medicine.presentation
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
