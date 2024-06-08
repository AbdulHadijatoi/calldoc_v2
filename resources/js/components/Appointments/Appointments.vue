<template>
  <v-app>
    <div data-app>
      <div class="card add-product card--content-center">
        <div class="card__wrapper">
          <!-- <v-container class="col-12"> -->
            <v-row class="p-2 d-flex justify-content-between">
              <v-col cols="12" sm="12" lg="6" xl="6">
                <v-text-field
                  v-model="search"
                  variant="outlined"
                  density="compact"
                  label="Search Appointment"
                  @keyup.enter="getData()"
                >
                  <template v-slot:append-inner>
                    <v-icon @click="getData">
                      <i class="fas fa-search"></i>
                    </v-icon>
                  </template>
                </v-text-field>
              </v-col>
              <v-btn class="ml-2" color="primary" outlined elevation="0" @click="downloadExcel">
                <i class="fa fa-download mr-2"></i> Excel
              </v-btn>
            </v-row>
            <br/>
            <v-data-table-server
              density="compact"
              v-model:page="currentPage"
              v-model="selectedRows"
              :items-length="totalItems"
              v-model:items-per-page="itemsPerPage"
              :loading="loading"
              :headers="headers"
              :items="appointments"
              class="elevation-0 sticky-table no-wrap-table"
              show-select return-object
            >
              <template v-slot:item.created_date="{ item }">
                <div>{{ item.created_at.split(' ')[0] }}</div>
              </template>
              <template v-slot:item.created_time="{ item }">
                <div>{{ item.created_at.split(' ')[1] }}</div>
              </template>

              <template v-slot:item.payment_status="{ item }">
                <span v-if="item.payment_status == 1" class="btn btn-sm btn-success">Payé</span>
                <span v-else class="btn btn-sm btn-danger">Restant</span>
              </template>

              <template v-slot:item.note="{ item }">
                <span v-if="item.note != null && item.note != 'NO' && item.note != 'no'" class="btn btn-sm btn-sm btn-danger">{{ item.note }}</span>
                <span v-else>{{ item.note }}</span>
              </template>
              <template v-slot:item.appointment_status="{ item }">
                <span v-if="item.appointment_status.toLowerCase() === 'pending'" class="badge badge-pill bg-warning-light">En attente</span>
                <span v-else-if="['approved', 'approve'].includes(item.appointment_status.toLowerCase())" class="badge badge-pill bg-success-light">Approuvé</span>
                <span v-else-if="['canceled', 'cancel'].includes(item.appointment_status.toLowerCase())" class="badge badge-pill bg-danger-light">Annulé</span>
                <span v-else-if="['completed', 'complete'].includes(item.appointment_status.toLowerCase())" class="badge badge-pill bg-default-light">Complété</span>
              </template>

              <template v-if="isDoctor == 1" v-slot:item.change_status="{ item }">
                <a v-if="['approve', 'complete'].includes(item.appointment_status.toLowerCase())" :href="`/completeAppointment/${item.id}`" class="btn btn-sm bg-info-light" :class="{ 'disabled': item.appointment_status.toLowerCase() === 'complete' }">
                  <i class="fas fa-check"></i> Complet
                </a>
                <div v-else-if="['pending', 'cancel'].includes(item.appointment_status.toLowerCase())">
                  <a :href="`/acceptAppointment/${item.id}`" class="btn btn-sm bg-success-light" :class="{ 'disabled': item.appointment_status.toLowerCase() !== 'pending' }">
                    <i class="fas fa-check"></i> Accepter
                  </a>
                  <a :href="`/cancelAppointment/${item.id}`" class="btn btn-sm bg-danger-light ml-2" :class="{ 'disabled': item.appointment_status.toLowerCase() !== 'pending' }">
                    <i class="fas fa-times"></i> Annuler
                  </a>
                </div>
              </template>

              <template v-slot:item.add_prescription="{ item }">
                <a v-if="item.prescription == 0" :href="`/prescription/${item.id}`" class="btn btn-sm bg-success-light">
                  <i class="fas fa-plus"></i> Ajouter une ordonnance
                </a>
                <a v-else :href="`/prescription/upload/${item.preData.pdf}`" class="btn btn-sm bg-success-light" data-fancybox="gallery2">
                  afficher l'ordonnance
                </a>
              </template>
              <template v-slot:item.create_zoom_meeting="{ item }">
                <a :href="`/create_zoom_metting/${item.id}`" class="btn btn-sm bg-primary-light">
                  Créer une réunion
                </a>
              </template>

              <template v-slot:item.actions="{ item }">
                <v-tooltip location="top" text="View">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon v-bind="attrs" v-on="on" color="info" @click="viewAppointment(item.id)">
                      <i class="far fa-eye"></i>
                    </v-icon>
                  </template>
                </v-tooltip>
                <v-tooltip v-if="isDoctor == 1 && item.is_from == 1" location="top" text="Edit">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon v-bind="attrs" v-on="on" class="mx-2" color="success" @click="editAppointment(item.id)">
                      <i class="far fa-edit"></i>
                    </v-icon>
                  </template>
                </v-tooltip>
                <v-tooltip v-if="isDoctor == 1 && item.is_from == 1" location="top" text="Delete">
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon v-bind="attrs" v-on="on" color="danger" @click="confirmDelete(item.id)">
                      <i class="far fa-trash-alt"></i>
                    </v-icon>
                  </template>
                </v-tooltip>
              </template>
            </v-data-table-server>

            <!-- Delete Confirmation Dialog -->
            <v-dialog v-model="deleteDialog" max-width="500px">
              <v-card>
                <v-card-title class="headline">Confirm Delete</v-card-title>
                <v-card-text>Are you sure you want to delete this appointment?</v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="green darken-1" text @click="deleteDialog = false">Cancel</v-btn>
                  <v-btn color="red darken-1" text @click="confirmDeleteAction">Delete</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          <!-- </v-container> -->
        </div>
      </div>
    </div>


    <v-dialog v-model="appointmentDialog" max-width="800px">
  <v-card class="appointment-card">
    <v-card-title class="headline">Appointment Details</v-card-title>
    <v-card-text>
      <div v-if="appointmentDetails">
        <v-row>
          <v-col cols="12" md="6">
            <strong class="highlight">Appointment ID:</strong> <span>{{ appointmentDetails.appointment_id }}</span><br>
            <strong class="highlight">Patient Name:</strong> <span>{{ appointmentDetails.patient_name }}</span><br>
            <strong class="highlight">Note:</strong> <span>{{ appointmentDetails.note }}</span><br>
            <strong class="highlight">Amount:</strong> <span>{{ appointmentDetails.amount }}</span><br>
            <strong class="highlight">Date:</strong> <span>{{ appointmentDetails.date }}</span><br>
            <strong class="highlight">Time:</strong> <span>{{ appointmentDetails.time }}</span><br>
            <strong class="highlight">Payment Type:</strong> <span>{{ appointmentDetails.payment_type }}</span><br>
            <strong class="highlight">Appointment For:</strong> <span>{{ appointmentDetails.appointment_for }}</span><br>
            <strong class="highlight">Patient Age:</strong> <span>{{ appointmentDetails.age }}</span><br>
            <strong class="highlight">Payment Status:</strong> <span>{{ appointmentDetails.payment_status ? 'Paid' : 'Unpaid' }}</span><br>
            <strong class="highlight">Appointment Status:</strong> <span>{{ appointmentDetails.appointment_status }}</span><br>
            <strong class="highlight">Illness Information:</strong> <span>{{ appointmentDetails.illness_information }}</span><br>
            <strong class="highlight">Doctor Commission:</strong> <span>{{ appointmentDetails.doctor_commission }}</span><br>
            <strong class="highlight">Admin Commission:</strong> <span>{{ appointmentDetails.admin_commission }}</span><br>
            <strong class="highlight">Cancel Reason:</strong> <span>{{ appointmentDetails.cancel_reason }}</span><br>
          </v-col>
          <v-col cols="12" md="6">
            <strong class="highlight">Doctor Information:</strong><br>
            <strong>Name:</strong> <span>{{ appointmentDetails.doctor.name }}</span><br>
            <strong>Description:</strong> <span>{{ appointmentDetails.doctor.desc }}</span><br>
            <!-- Display other doctor details here -->
            <br>
            <strong class="highlight">Patient Address:</strong><br>
            <strong>Address:</strong> <span>{{ appointmentDetails.address.address }}</span><br>
            <strong>Latitude:</strong> <span>{{ appointmentDetails.address.lat }}</span><br>
            <strong>Longitude:</strong> <span>{{ appointmentDetails.address.lang }}</span><br>
            <!-- Display other address details here -->
            <br>
            <strong class="highlight">Hospital Information:</strong><br>
            <strong>Name:</strong> <span>{{ appointmentDetails.hospital.name }}</span><br>
            <strong>Phone:</strong> <span>{{ appointmentDetails.hospital.phone }}</span><br>
            <strong>Address:</strong> <span>{{ appointmentDetails.hospital.address }}</span><br>
            <!-- Display other hospital details here -->
          </v-col>
        </v-row>
      </div>
    </v-card-text>
    <v-card-actions>
      <v-btn color="blue darken-1" text @click="appointmentDialog = false">Close</v-btn>
    </v-card-actions>
  </v-card>
</v-dialog>



  </v-app>
</template>

<script>
import { ref, onMounted, onBeforeMount, watch, computed, isProxy, toRaw } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx';

export default {
  props: {
    type: {
      type: String,
      required: true
    },
  },
  setup(props) {
    const loading = ref(false);
    const appointments = ref([]);
    const selectedStatus = ref([]);
    const currentPage = ref(1);
    const itemsPerPage = ref(10);
    const totalItems = ref(0);
    const dateRange = ref();
    const selectedRows = ref([]);
    const selectedIds = ref([]);
    const toggleSelect = ref(false);
    const search = ref();
    const isDoctor = ref(0);

    const deleteDialog = ref(false);
    const deleteId = ref(null);
    const appointmentDialog = ref(false);
    const appointmentDetails = ref({});

    const headers = [
      {title: 'Numéro de rendez-vous', key: 'appointment_id', align: 'start'},
      {title: 'Nom du patient', key: 'patient_name', align: 'start'},
      {title: 'Note', key: 'note', align: 'start'},
      {title: 'Montant', key: 'amount', align: 'start'},
      {title: 'Date', key: 'date', align: 'start'},
      {title: 'Statut de paiement', key: 'payment_status', align: 'start'},
      {title: 'Statut', key: 'appointment_status', align: 'start'},
      {title: 'Changer le statut', key: 'change_status', align: 'start'},
      {title: 'Ajouter une ordonnance', key: 'add_prescription', align: 'start'},
      {title: 'Créer une réunion Zoom', key: 'create_zoom_meeting', align: 'start'},
      {title: 'Action', key: "actions", align: 'center'},
    ];

    const getData = async () => {
      loading.value = true;
      try {
        const response = await axios.post('/get-appointments-data', {
          type: props.type,
          search: search.value,
        });
        appointments.value = response.data.appointments.data;
        totalItems.value = response.data.total_appointments;
        isDoctor.value = response.data.is_doctor;
        loading.value = false;
      } catch (error) {
        console.error('Error:', error);
        loading.value = false;
      }
    };
    
    const downloadExcel = () => {
      const worksheet = XLSX.utils.json_to_sheet(appointments.value);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Appointments');
      
      const timestamp = new Date().toISOString().replace(/[-:.]/g, '');
      const filename = `appointments_${timestamp}.xlsx`;

      XLSX.writeFile(workbook, filename);
    };

    const confirmDelete = (appointmentId) => {
      deleteId.value = appointmentId;
      deleteDialog.value = true;
    };

    const confirmDeleteAction = () => {
      deleteAppointment(deleteId.value);
      deleteDialog.value = false;
    };

    const deleteAppointment = (appointmentId) => {
      window.location.href = `/delete_appointment/${appointmentId}`;
    };

    const editAppointment = (appointmentId) => {
      window.location.href = `/edit_appointment/${appointmentId}`;
    };

    const viewAppointment = async (appointmentId) => {
      try {
        const response = await axios.get(`/show_appointment/${appointmentId}`);
        appointmentDetails.value = response.data.data;
        appointmentDialog.value = true;
      } catch (error) {
        console.error('Error:', error);
      }
    };

    const clearFilters = () => {
      selectedStatus.value = null;
      dateRange.value = null;
      currentPage.value = 1;
      getData();
    };

    onBeforeMount(() => {
    });

    onMounted(() => {
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - 30);

      const endDate = new Date();
      dateRange.value = [startDate, endDate];
    });

    const formatDate = (date) => {
      if (!date) return '';
      const year = date.getFullYear();
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const day = date.getDate().toString().padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    watch(dateRange, () => {
      currentPage.value = 1;
      getData();
    });

    watch(selectedStatus, () => {
      currentPage.value = 1;
      getData();
    });

    watch(currentPage, () => {
      getData();
    });

    watch(selectedRows, (newSelectedRows) => {
      selectedIds.value = newSelectedRows.map(row => row.r_id);
      if (isProxy(selectedIds.value)){
        const rawData = toRaw(selectedIds.value)
        console.log(rawData);
      }
    });

    watch(itemsPerPage, (newItemsPerPage) => {
      currentPage.value = 1;
      getData();
    });

    return {
      loading, getData, appointments, selectedStatus, currentPage, itemsPerPage,
      clearFilters, totalItems, headers, dateRange, formatDate, isDoctor,
      selectedRows, selectedIds, toggleSelect, confirmDelete, deleteAppointment,
      viewAppointment, editAppointment, search, deleteDialog, confirmDeleteAction,
      appointmentDialog, appointmentDetails, downloadExcel
    };
  }
}
</script>

<style>
.v-icon--size-default {
  font-size: calc(var(--v-icon-size-multiplier) * 1.2em) !important;
}
.sticky-table .v-data-table__wrapper {
  overflow-x: auto;
}

.sticky-table th:first-child, .sticky-table td:first-child {
  position: sticky;
  left: 0;
  background: #eff2f6;
  z-index: 2;
  box-shadow: 2px 0 5px -2px #888 !important;
}

.sticky-table th:last-child, .sticky-table td:last-child {
  position: sticky;
  right: 0;
  background: #eff2f6;
  z-index: 2;
  box-shadow: -2px 0 5px -2px #888 !important;
}
.no-wrap-table th, .no-wrap-table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
/* Alternate Row Colors */
.sticky-table tr th {
  font-weight: bold;
  font-size: 12px;
}
.sticky-table tr td {
  background-color: #fbfbfb;
}
</style>
