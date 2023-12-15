<template>
    <div class="row table-sm mr-0 ml-0 p-0">
        <div class="crm-content-header d-flex w-100 mb-2">
            <div class="crm-content-header-left d-flex w-50">
                <div class="crm-content-header-left-item mr-3" style="width:160px">
                    <h5 :class="mode ? 'text__color5' : 'text__color1'" class="d-inline mr-2 font-weight-bold">{{
                        $t("message.operations")
                    }}</h5>
                    <crm-refresh :class="mode ? 'text__color5' : 'text__color1'" @c-click="refresh()"></crm-refresh>
                </div>
                <div class="crm-content-header-left-item">
                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" :placeholder="$t('message.search')"
                        prefix-icon="el-icon-search" v-model="filterForm.search" clearable></el-input>
                </div>
                <div class="crm-content-header-left-item">
                    <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.from_date" type="date"
                        :format="date_format" :value-format="date_format" size="mini" :placeholder="$t('message.from')">
                    </el-date-picker>
                </div>
                <div class="crm-content-header-left-item">
                    <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.to_date" type="date"
                        :format="date_format" :value-format="date_format" size="mini" :placeholder="$t('message.to')">
                    </el-date-picker>
                </div>
            </div>
            <div class="crm-content-header-right d-flex w-50 justify-content-end">
                <div class="crm-content-header-right-item">
                    <el-button :class="mode ? 'mode_btn1' : 'mode__btn2'" v-can="'founderOperations.create'" size="mini"
                        @click="drawerCreate = true" icon="el-icon-circle-plus-outline"> {{ $t('message.create') }}
                    </el-button>
                </div>
                <div class="crm-content-header-right-item">
                    <export-excel :data="list" :fields="excel_fields" @fetch="controlExcelData()"
                        :worksheet="$t('message.operations')" :name="$t('message.operations')">
                        <el-button :class="mode ? 'mode_btn1' : 'mode__btn2'" size="mini">
                            <i class="el-icon-document-delete"></i> {{ $t('message.download_excel') }}
                        </el-button>
                    </export-excel>
                </div>
                <div class="crm-content-header-right-item">
                    <crm-column-settings :class="mode ? 'mode_dr1' : 'mode__dr2'" :modelName="'founderOperations'"
                        :columns="columns" :defaultColumns="defaultColumns"
                        @c-change="updateColumn"></crm-column-settings>
                </div>
            </div>
        </div>

        <!-- STARTS TABLE -->
        <div class="table-wrap" :class="{ dark: !mode }">
            <table :class="mode ? 'table__day' : 'table__night'" v-loading="loadingData"
                :element-loading-text="$t('message.loading')" element-loading-spinner="el-icon-loading">
                <thead>
                    <tr v-if="dataTableHeader && dataTableHeader.length > 0">
                        <crm-th v-for="key in dataTableHeader" :key="key" v-bind="{
                            sort: sort,
                            column: columns[key],
                        }" @c-change="updateSort" />
                    </tr>
                    <tr v-if="dataTableHeader && dataTableHeader.length > 0">
                        <template v-for="key in dataTableHeader">
                            <th v-if="columns[key] && columns[key].show" :key="key">
                                <span v-if="key == 'id'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" clearable size="mini"
                                        class="id_input" v-model="filterForm.id"
                                        :placeholder="columns.id.title"></el-input>
                                </span>
                                <span v-else-if="key == 'founder_id'">
                                    <founders :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.founder_id"
                                        :founder_id="filterForm.founder_id" size="mini"></founders>
                                </span>
                                <span v-else-if="key == 'start_date'">
                                    <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.start_date"
                                        :placeholder="columns.start_date.title" size="mini" :value-format="date_format">
                                    </el-date-picker>
                                </span>
                                <span v-else-if="key == 'operation_type_id'">
                                    <el-select :class="mode ? 'mode_1' : 'mode__2'"
                                        v-model="filterForm.operation_type_id" filterable clearable
                                        :placeholder="columns.operation_type_id.title" size="mini">
                                        <el-option v-for="(operation_type, index) in operationTypes"
                                            :key="index + 'operation_types'" :label="operation_type.name"
                                            :value="operation_type.id"></el-option>
                                    </el-select>
                                </span>
                                <span v-else-if="key == 'investment_type_id'">
                                    <el-select :class="mode ? 'mode_1' : 'mode__2'"
                                        v-model="filterForm.investment_type_id" filterable clearable
                                        :placeholder="columns.investment_type_id.title" size="mini">
                                        <el-option v-for="(investment_type, index) in investmentTypes"
                                            :key="index + 'investment_types'" :label="investment_type.name"
                                            :value="investment_type.id"></el-option>
                                    </el-select>
                                </span>
                                <span v-else-if="key == 'output_type_id'">
                                    <el-select :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.output_type_id"
                                        filterable clearable :placeholder="columns.output_type_id.title" size="mini">
                                        <el-option v-for="(output_type, index) in outputTypes"
                                            :key="index + 'output_types'" :label="output_type.name"
                                            :value="output_type.id"></el-option>
                                    </el-select>
                                </span>
                                <span v-else-if="key == 'total_price'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini"
                                        v-model="filterForm.total_price" :placeholder="columns.total_price.title"
                                        clearable>
                                    </el-input>
                                </span>
                                <span v-else-if="key == 'currency_id'">
                                    <currencies :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.currency_id"
                                        :currency_id="filterForm.currency_id" size="mini"></currencies>
                                </span>
                                <span v-else-if="key == 'rate'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" v-model="filterForm.rate"
                                        :placeholder="columns.rate.title" clearable>
                                    </el-input>
                                </span>
                                <span v-else-if="key == 'created_at'">
                                    <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.created_at"
                                        :placeholder="columns.created_at.title" size="mini" :value-format="date_format">
                                    </el-date-picker>
                                </span>
                                <span v-else-if="key == 'updated_at'">
                                    <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.updated_at"
                                        :placeholder="columns.updated_at.title" size="mini" :value-format="date_format">
                                    </el-date-picker>
                                </span>
                                <span v-else-if="key == 'performed'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" disabled></el-input>
                                </span>
                                <span v-else-if="key == 'settings'"></span>
                            </th>
                        </template>
                    </tr>

                    <!-- <tr>
                    <crm-th :column="columns.id" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.founder_id" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.start_date" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.operation_type_id" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.investment_type_id" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.output_type_id" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.total_price" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.currency_id" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.rate" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :sort="sort" :column="columns.created_at" @c-change="updateSort"></crm-th>
                    <crm-th :sort="sort" :column="columns.updated_at" @c-change="updateSort"></crm-th>
                    <crm-th :column="columns.performed" :sort="sort" @c-change="updateSort"></crm-th>
                    <crm-th :sort="sort" :column="columns.settings" @c-change="updateSort"></crm-th>
                </tr>
                <tr>
                    <th v-if="columns.id.show">
                        <el-input :class="mode ? 'mode_1' : 'mode__2'" clearable size="mini" class="id_input" v-model="filterForm.id" :placeholder="columns.id.title"></el-input>
                    </th>
                    <th v-if="columns.founder_id.show">
                        <founders :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.founder_id" :founder_id="filterForm.founder_id" size="mini"></founders>
                    </th>
                    <th v-if="columns.start_date.show">
                        <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.start_date" :placeholder="columns.start_date.title" size="mini" :value-format="date_format"> </el-date-picker>
                    </th>
                    <th v-if="columns.operation_type_id.show">
                        <el-select :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.operation_type_id" filterable clearable :placeholder="columns.operation_type_id.title" size="mini">
                            <el-option v-for="(operation_type, index) in operationTypes" :key="index + 'operation_types'" :label="operation_type.name" :value="operation_type.id"></el-option>
                        </el-select>
                    </th>
                    <th v-if="columns.investment_type_id.show">
                        <el-select :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.investment_type_id" filterable clearable :placeholder="columns.investment_type_id.title" size="mini">
                            <el-option v-for="(investment_type, index) in investmentTypes" :key="index + 'investment_types'" :label="investment_type.name" :value="investment_type.id"></el-option>
                        </el-select>
                    </th>
                    <th v-if="columns.output_type_id.show">
                        <el-select :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.output_type_id" filterable clearable :placeholder="columns.output_type_id.title" size="mini">
                            <el-option v-for="(output_type, index) in outputTypes" :key="index + 'output_types'" :label="output_type.name" :value="output_type.id"></el-option>
                        </el-select>
                    </th>
                    <th v-if="columns.total_price.show">
                        <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" v-model="filterForm.total_price" :placeholder="columns.total_price.title" clearable>
                        </el-input>
                    </th>
                    <th v-if="columns.currency_id.show">
                        <currencies :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.currency_id" :currency_id="filterForm.currency_id" size="mini"></currencies>
                    </th>
                    <th v-if="columns.rate.show">
                        <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" v-model="filterForm.rate" :placeholder="columns.rate.title" clearable>
                        </el-input>
                    </th>
                    <th v-if="columns.created_at.show">
                        <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.created_at" :placeholder="columns.created_at.title" size="mini"  :value-format="date_format">
                        </el-date-picker>
                    </th>
                    <th v-if="columns.updated_at.show">
                        <el-date-picker :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.updated_at" :placeholder="columns.updated_at.title" size="mini" :value-format="date_format">
                        </el-date-picker>
                    </th>
                    <th v-if="columns.performed.show">
                        <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" disabled></el-input>
                    </th>
                    <th v-if="columns.settings.show"></th>
                </tr> -->
                </thead>
                <transition-group name="flip-list" tag="tbody">
                    <tr v-for="founderOperation in list" :key="founderOperation.id">
                        <template v-for="key in dataTableHeader">
                            <td v-if="columns[key].show" :key="key">
                                <span v-if="key == 'id'">{{ founderOperation.id }}</span>
                                <span v-else-if="key == 'founder_id'">{{ (founderOperation.founder &&
                                    founderOperation.founder.user) ? founderOperation.founder.user.name : ''
                                }}</span>
                                <span v-else-if="key == 'start_date'">{{ founderOperation.start_date }}</span>
                                <span v-else-if="key == 'operation_type_id'">{{
                                    founderOperation.operation_type ?
                                        founderOperation.operation_type.name : ''
                                }}</span>
                                <span v-else-if="key == 'investment_type_id'">
                                    <template
                                        v-if="founderOperation.operation_type_id == 1 && founderOperation.investment_type_id == 2">
                                        <span @click="showNomenclatures(founderOperation)" class="cursor-pointer"
                                            style="color: #3490dc;">
                                            {{
                                                founderOperation.investment_type ? founderOperation.investment_type.name
                                                    : ''
                                            }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        {{
                                            founderOperation.investment_type ? founderOperation.investment_type.name : ''
                                        }}
                                    </template>
                                </span>
                                <span v-else-if="key == 'output_type_id'">{{
                                    founderOperation.output_type ?
                                        founderOperation.output_type.name : ''
                                }}</span>
                                <span v-else-if="key == 'total_price'">{{
                                    founderOperation.total_price | formatNumber(2)
                                }}</span>
                                <span v-else-if="key == 'currency_id'">{{
                                    founderOperation.currency ?
                                        founderOperation.currency.name : ''
                                }}</span>
                                <span v-else-if="key == 'rate'">{{ founderOperation.rate | formatNumber }}</span>
                                <span v-else-if="key == 'created_at'">{{
                                    founderOperation.created_at | dateFormat
                                }}</span>
                                <span v-else-if="key == 'updated_at'">{{
                                    founderOperation.updated_at | dateFormat
                                }}</span>
                                <span v-else-if="key == 'performed'" class="settings-td">
                                    <el-button size="mini" round :type="performedBtnType(founderOperation)">{{
                                        $t("message.performed")
                                    }} ({{ founderOperation.performed | formatNumber(2) }}
                                        %)</el-button>
                                </span>
                                <span v-else-if="key == 'settings'" class="settings-td">
                                    <crm-setting-dropdown :class="mode ? 'mode__st1' : 'mode__st2'"
                                        :model="founderOperation" name="founderOperations" :actions="actions"
                                        @edit="edit" @delete="destroy">
                                    </crm-setting-dropdown>
                                </span>
                            </td>
                        </template>
                        <!-- <td v-if="columns.id.show">{{ founderOperation.id }}</td>
                    <td v-if="columns.founder_id.show">{{ (founderOperation.founder && founderOperation.founder.user) ? founderOperation.founder.user.name : '' }}</td>
                    <td v-if="columns.start_date.show">{{ founderOperation.start_date }}</td>
                    <td v-if="columns.operation_type_id.show">{{ founderOperation.operation_type ? founderOperation.operation_type.name : '' }}</td>
                    <td v-if="columns.investment_type_id.show">
                        <template v-if="founderOperation.operation_type_id == 1 && founderOperation.investment_type_id == 2">
                            <span @click="showNomenclatures(founderOperation)" class="cursor-pointer" style="color: #3490dc;">
                                {{ founderOperation.investment_type ? founderOperation.investment_type.name : '' }}
                            </span>
                        </template>
                        <template v-else>
                            {{ founderOperation.investment_type ? founderOperation.investment_type.name : '' }}
                        </template>
                    </td>
                    <td v-if="columns.output_type_id.show">{{ founderOperation.output_type ? founderOperation.output_type.name : '' }}</td>
                    <td v-if="columns.total_price.show">{{ founderOperation.total_price | formatNumber(2) }}</td>
                    <td v-if="columns.currency_id.show">{{ founderOperation.currency ? founderOperation.currency.name : '' }}</td>
                    <td v-if="columns.rate.show">{{ founderOperation.rate | formatNumber }}</td>
                    <td v-if="columns.created_at.show">{{ founderOperation.created_at | dateFormat }}</td>
                    <td v-if="columns.updated_at.show">{{ founderOperation.updated_at | dateFormat }}</td>
                    <td v-if="columns.performed.show" class="settings-td">
                        <el-button size="mini" round :type="performedBtnType(founderOperation)">{{ $t("message.performed") }} ({{ founderOperation.performed | formatNumber(2) }} %)</el-button>
                    </td>
                    <td v-if="columns.settings.show" class="settings-td">
                        <crm-setting-dropdown :class="mode ? 'mode__st1' : 'mode__st2'" :model="founderOperation" name="founderOperations" :actions="actions" @edit="edit" @delete="destroy">
                        </crm-setting-dropdown>
                    </td> -->
                    </tr>
                </transition-group>
            </table>
        </div>
        <crm-pagination :class="mode ? 'mode__pg1' : 'mode__pg2'" :pagination="pagination"
            @c-change="updatePagination"></crm-pagination>
        <!-- ENDS TABLE -->

        <el-dialog :title="$t('message.nomenclatures')" :visible.sync="visibleNomenclatures" width="80%"
            @closed="emptyNomenclatures()">
            <el-card class="box-card pb-2 p-1">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.products') }}</span>
                </div>
                <table class="table" v-loading="loadingNomenclatures">
                    <thead>
                        <tr class="crm-table-header-border-0">
                            <th>{{ $t("message.name") }}</th>
                            <th>{{ $t("message.quantity") }}</th>
                            <th>{{ $t("message.measurement") }}</th>
                            <th>{{ $t("message.price") }}</th>
                            <th>{{ $t("message.currency") }}</th>
                            <th>{{ $t("message.rate") }}</th>
                            <td>{{ $t('message.summa') }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in product_items" :key="index + '-product_items'">
                            <td>{{ item.product ? item.product.name : "" }}</td>
                            <td>{{ item.quantity | formatNumber }}</td>
                            <td>
                                {{ item.product ? item.product.measurement ? item.product.measurement.name : '' : '' }}
                            </td>
                            <td>{{ item.price | formatNumber }}</td>
                            <td>{{ item.currency ? item.currency.name : "" }}</td>
                            <td>{{ item.rate | formatNumber }}</td>
                            <td>{{ (item.quantity * item.price) | formatNumber }} {{
                                item.currency ?
                                    item.currency.symbol : ''
                            }}</td>
                        </tr>
                    </tbody>
                </table>
            </el-card>

            <el-card class="box-card pb-2 p-1 mt-3">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.materials') }}</span>
                </div>
                <table class="table" v-loading="loadingNomenclatures">
                    <thead>
                        <tr class="crm-table-header-border-0">
                            <th>{{ $t("message.name") }}</th>
                            <th>{{ $t("message.quantity") }}</th>
                            <th>{{ $t("message.measurement") }}</th>
                            <th>{{ $t("message.price") }}</th>
                            <th>{{ $t("message.currency") }}</th>
                            <th>{{ $t("message.rate") }}</th>
                            <td>{{ $t('message.summa') }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in material_items" :key="index + '-material_items'">
                            <td>{{ item.material ? item.material.name : "" }}</td>
                            <td>{{ item.quantity | formatNumber }}</td>
                            <td>
                                {{
                                    item.material ? item.material.measurement ? item.material.measurement.name : '' : ''
                                }}
                            </td>
                            <td>{{ item.price | formatNumber }}</td>
                            <td>{{ item.currency ? item.currency.name : "" }}</td>
                            <td>{{ item.rate | formatNumber }}</td>
                            <td>{{ (item.quantity * item.price) | formatNumber }} {{
                                item.currency ?
                                    item.currency.symbol : ''
                            }}</td>
                        </tr>
                    </tbody>
                </table>
            </el-card>
        </el-dialog>

        <el-drawer :with-header="false" :visible.sync="drawerCreate" size="90%" ref="drawerCreate"
            @opened="drawerOpened('drawerCreateChild')" @closed="drawerClosed('drawerCreateChild')">
            <crm-create ref="drawerCreateChild" drawer="drawerCreate"></crm-create>
        </el-drawer>

        <el-drawer :with-header="false" :visible.sync="drawerUpdate" size="90%" ref="drawerUpdate"
            @opened="drawerOpened('drawerUpdateChild')" @closed="drawerClosed('drawerUpdateChild')">
            <crm-update :founderOperation="selectedItem" ref="drawerUpdateChild" drawer="drawerUpdate"></crm-update>
        </el-drawer>
    </div>
</template>
<script>
import { mapGetters, mapActions, mapState } from "vuex";
import CrmCreate from "./components/crm-create";
import CrmUpdate from "./components/crm-update";
import list from "@/utils/mixins/list";
import founders from '@inventory/crm-founder-select';
import currencies from '@inventory/crm-currency-select';
import { formatNumber } from '@/filters';

export default {
    mixins: [list],
    components: { CrmCreate, CrmUpdate, founders, currencies },
    data() {
        return {
            visibleNomenclatures: false,
            loadingNomenclatures: false,
            product_items: [],
            material_items: [],
        };
    },
    computed: {
        ...mapState({
            defaultColumns: state => state.founderOperations.defaultColumns
        }),
        ...mapGetters({
            list: 'founderOperations/list',
            columns: "founderOperations/columns",
            pagination: "founderOperations/pagination",
            filter: "founderOperations/filter",
            sort: "founderOperations/sort",
            operationTypes: 'founderOperations/operationTypes',
            investmentTypes: 'founderOperations/investmentTypes',
            outputTypes: 'founderOperations/outputTypes',
            mode: "MODE"
        }),
        actions: function () {
            return ['edit', 'delete'];
        },
        dataTableHeader() {
            return Object.keys(this.columns)
        }
    },
    mounted() {
        if (_.isFunction(this.controlExcelData)) {
            this.controlExcelData();
        }
        if (!_.size(this.operationTypes)) this.loadOperationTypes()
        if (!_.size(this.investmentTypes)) this.loadInvestmentTypes()
        if (!_.size(this.outputTypes)) this.loadOutputTypes()
    },
    methods: {
        ...mapActions({
            updateList: 'founderOperations/index',
            updateSort: "founderOperations/updateSort",
            updateFilter: "founderOperations/updateFilter",
            updateColumn: "founderOperations/updateColumn",
            updatePagination: "founderOperations/updatePagination",
            empty: 'founderOperations/empty',
            delete: 'founderOperations/destroy',
            refreshData: 'founderOperations/refreshData',

            loadOperationTypes: 'founderOperations/getOperationTypes',
            loadInvestmentTypes: 'founderOperations/getInvestmentTypes',
            loadOutputTypes: 'founderOperations/getOutputTypes',

            getNomenclatures: 'founderOperations/getNomenclatures',
        }),
        performedBtnType(founderOperation) {
            if (founderOperation.performed <= 0) {
                return "danger";
            }
            else if (founderOperation.performed > 0 && founderOperation.performed < 100) {
                return "warning";
            }
            else {
                return "success";
            }
        },
        edit(model) {
            // ispolneniya 0 bolsa yani pul otqazilmagan yoki materialla priyom qilinmagan bolsa izmenit qila oladi
            if (model.performed == 0) {
                this.selectedItem = model;
                this.drawerUpdate = true;
            }
            else {
                this.$message({
                    message: this.$t('message.Cant be changed Completed more than zero'),
                    type: 'warning',
                    showClose: true,
                });
            }
        },
        destroy(model) {
            // ispolneniya 0 bolsa yani pul otqazilmagan yoki materialla priyom qilinmagan bolsa udalit qila oladi
            if (model.performed == 0) {
                this.delete(model.id)
                    .then(res => {
                        this.$alert(res);
                        this.fetchData()
                    })
                    .catch(err => {
                        this.$alert(err);
                    })
            }
            else {
                this.$message({
                    message: this.$t('message.Cant be delete Completed more than zero'),
                    type: 'warning',
                    showClose: true,
                });
            }
        },
        showNomenclatures(model) {
            this.visibleNomenclatures = true;
            this.loadingNomenclatures = true;

            this.getNomenclatures(model.id)
                .then((res) => {
                    this.product_items = res ? res.product_items : [];
                    this.material_items = res ? res.material_items : [];
                    this.loadingNomenclatures = false;
                })
                .catch((err) => {
                    this.loadingNomenclatures = false;
                    this.$alert(err);
                });
        },
        emptyNomenclatures() {
            this.product_items = [];
            this.material_items = [];
        },
        controlExcelData() {
            this.excel_fields = {};
            for (let key in this.columns) {
                if (this.columns.hasOwnProperty(key)) {
                    let column = this.columns[key];
                    if (column.show && column.column !== 'settings') {
                        switch (column.column) {
                            case 'founder_id':
                                this.excel_fields[column.title] = 'founder.user.name'; break;
                            case 'operation_type_id':
                                this.excel_fields[column.title] = 'operation_type.name'; break;
                            case 'investment_type_id':
                                this.excel_fields[column.title] = 'investment_type.name'; break;
                            case 'output_type_id':
                                this.excel_fields[column.title] = 'output_type.name'; break;
                            case 'total_price':
                                this.excel_fields[column.title] = {
                                    field: 'total_price',
                                    callback: (value) => {
                                        return formatNumber(value);
                                    }
                                };
                                break;
                            case 'currency_id':
                                this.excel_fields[column.title] = 'currency.name'; break;
                            case 'performed':
                                this.excel_fields[column.title] = {
                                    field: 'performed',
                                    callback: (value) => {
                                        return value + '%';
                                    }
                                };
                                break;
                            default:
                                this.excel_fields[column.title] = column.column; break;
                        }
                    }
                }
            }
        },
    },
    beforeRouteLeave(to, from, next) {
        this.$store.commit('founderOperations/EMPTY_LIST');
        next()
    },
};
</script>
