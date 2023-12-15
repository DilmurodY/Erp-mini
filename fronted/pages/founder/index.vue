<template>
    <div class="row table-sm mr-0 ml-0 p-0">
        <div class="crm-content-header d-flex w-100 mb-2">
            <div class="crm-content-header-left d-flex w-50">
                <div class="crm-content-header-left-item mr-3" style="width:160px">
                    <h5 :class="mode ? 'text__color5' : 'text__color1'" class="d-inline mr-2 font-weight-bold">{{
                        $t("message.founders")
                    }}</h5>
                    <crm-refresh :class="mode ? 'text__color5' : 'text__color1'" @c-click="refresh()"></crm-refresh>
                </div>
                <div class="crm-content-header-left-item">
                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" :placeholder="$t('message.search')"
                        prefix-icon="el-icon-search" v-model="filterForm.search" clearable></el-input>
                </div>
            </div>
            <div class="crm-content-header-right d-flex w-50 justify-content-end">
                <div class="crm-content-header-right-item">
                    <el-button :class="mode ? 'mode_btn1' : 'mode__btn2'" v-can="'founders.create'" size="mini"
                        @click="drawerCreate = true" icon="el-icon-circle-plus-outline"> {{ $t('message.create') }}
                    </el-button>
                </div>
                <div class="crm-content-header-right-item">
                    <export-excel :data="list" :fields="excel_fields" @fetch="controlExcelData()"
                        :worksheet="$t('message.founders')" :name="$t('message.founders')">
                        <el-button :class="mode ? 'mode_btn1' : 'mode__btn2'" size="mini">
                            <i class="el-icon-document-delete"></i> {{ $t('message.download_excel') }}
                        </el-button>
                    </export-excel>
                </div>
                <div class="crm-content-header-right-item">
                    <crm-column-settings :class="mode ? 'mode_dr1' : 'mode__dr2'" :modelName="'founders'"
                        :columns="columns" :defaultColumns="defaultColumns"
                        @c-change="updateColumn"></crm-column-settings>
                </div>
            </div>
        </div>
        <div :class="[{ propacity: showNavbar, }, mode ? 'table__fxday' : 'table__fxnight']"
            class="w-100 text-center mb-1 crm table__unfixed">
            <el-tag class="border-0" effect="plain">{{ $t("message.charter_capital") }}: {{ charter_capital |
                formatMoney(2)
            }}</el-tag>
        </div>
        <transition name="fade">
            <price-pr :charter_capital="charter_capital" v-if="showNavbar" />
        </transition>

        <!-- STARTS TABLE -->
        <div class="table-wrap" :class="{ dark: !mode }">
            <table :class="mode ? 'table__day' : 'table__night'" v-loading="loadingData"
                :element-loading-text="$t('message.loading')" element-loading-spinner="el-icon-loading">
                <thead>
                    <tr v-if="dataTableHeader && dataTableHeader.length > 0">
                        <crm-th v-for="key in dataTableHeader" :key="key" v-bind="{
                            sort: sort,
                            column: columns[key],
                            roleAmount: 'founders.index',
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
                                <span v-else-if="key == 'user_id'">
                                    <users :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.user_id"
                                        :user_id="filterForm.user_id" size="mini"></users>
                                </span>
                                <span v-else-if="key == 'total_price'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" disabled></el-input>
                                </span>
                                <span v-else-if="key == 'share_percentage'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini" disabled></el-input>
                                </span>
                                <span v-else-if="key == 'is_active'">
                                    <el-select :class="mode ? 'mode_1' : 'mode__2'" v-model="filterForm.is_active"
                                        filterable clearable :placeholder="columns.is_active.title" size="mini">
                                        <el-option :label="$t('message.yes')" :value="true"></el-option>
                                        <el-option :label="$t('message.no')" :value="false"></el-option>
                                    </el-select>
                                </span>
                                <span v-else-if="key == 'phone'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini"
                                        v-model="filterForm.phone" :placeholder="columns.phone.title" clearable>
                                    </el-input>
                                </span>
                                <span v-else-if="key == 'email'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini"
                                        v-model="filterForm.email" :placeholder="columns.email.title" clearable>
                                    </el-input>
                                </span>
                                <span v-else-if="key == 'comment'">
                                    <el-input :class="mode ? 'mode_1' : 'mode__2'" size="mini"
                                        v-model="filterForm.comment" :placeholder="columns.comment.title" clearable>
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
                                <span v-else-if="key == 'settings'"></span>
                            </th>
                        </template>
                    </tr>
                </thead>
                <transition-group name="flip-list" tag="tbody">
                    <tr v-for="founder in list" :key="founder.id">
                        <template v-for="key in dataTableHeader">
                            <td v-if="columns[key].show" :key="key">
                                <span v-if="key == 'id'">{{ founder.id }}</span>
                                <span v-else-if="key == 'user_id'">{{ founder.user ? founder.user.name : '' }}</span>
                                <span v-else-if="key == 'total_price'">{{ founder.total_price | formatMoney(2) }}</span>
                                <span v-else-if="key == 'share_percentage'">
                                    {{ founder.share_percentage | formatNumber(2) }} %
                                    <i @click="showSharePercentages(founder)" style="color: #3490dc;"
                                        class="el-icon-view cursor-pointer"></i>
                                </span>
                                <span v-else-if="key == 'is_active'">{{
                                    founder.is_active ? $t('message.yes') :
                                        t('message.no')
                                }}</span>
                                <span v-else-if="key == 'phone'">{{ founder.user ? founder.user.phone : '' }}</span>
                                <span v-else-if="key == 'email'">{{ founder.user ? founder.user.email : '' }}</span>
                                <span v-else-if="key == 'comment'">{{ founder.comment }}</span>
                                <span v-else-if="key == 'created_at'">{{ founder.created_at | dateFormat }}</span>
                                <span v-else-if="key == 'updated_at'">{{ founder.updated_at | dateFormat }}</span>
                                <span v-else-if="key == 'settings'" class="settings-td">
                                    <crm-setting-dropdown :class="mode ? 'mode__st1' : 'mode__st2'" :model="founder"
                                        name="founders" :actions="actions" @edit="edit" @delete="destroy">
                                    </crm-setting-dropdown>
                                </span>
                            </td>
                        </template>
                    </tr>
                </transition-group>
            </table>
        </div>
        <crm-pagination :class="mode ? 'mode__pg1' : 'mode__pg2'" :pagination="pagination"
            @c-change="updatePagination"></crm-pagination>
        <!-- ENDS TABLE -->

        <el-dialog :title="$t('message.share_percentage')" :visible.sync="visibleSharePercentages" width="80%"
            @closed="emptySharePercentages()">
            <table class="table" v-loading="loadingSharePercentages">
                <thead>
                    <tr class="crm-table-header-border-0">
                        <th>{{ $t("message.n") }}</th>
                        <th>{{ $t("message.operation_type") }}</th>
                        <th>{{ $t("message.investment_type") }}</th>
                        <th>{{ $t("message.Begin date") }}</th>
                        <th>{{ $t("message.end_date") }}</th>
                        <td>{{ $t('message.summa') }}</td>
                        <th>{{ $t("message.currency") }}</th>
                        <th>{{ $t("message.rate") }}</th>
                        <th>{{ $t("message.share_percentage") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in share_percentages" :key="index + '-share_percentages'">
                        <td>{{ index + 1 }}.</td>
                        <td>{{ item.operation_type ? item.operation_type.name : "" }}</td>
                        <td>{{ item.investment_type ? item.investment_type.name : "" }}</td>
                        <td>{{ item.start_date }}</td>
                        <td>{{ item.end_date }}</td>
                        <td>{{ item.total_price | formatNumber }}</td>
                        <td>{{ item.currency ? item.currency.name : "" }}</td>
                        <td>{{ item.rate | formatNumber }}</td>
                        <td>
                            {{ item.current_share_percentage | formatNumber }} %
                            <template v-if="!item.is_first_operation">
                                <template v-if="item.change_of_share_percentage > 0">
                                    <span style="color: #11D070">(+{{ item.change_of_share_percentage }}) %</span>
                                </template>
                                <template v-else-if="item.change_of_share_percentage < 0">
                                    <span style="color: #EA6966">({{ item.change_of_share_percentage }}) %</span>
                                </template>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </el-dialog>
        <el-drawer :with-header="false" :visible.sync="drawerCreate" size="60%" ref="drawerCreate"
            @opened="drawerOpened('drawerCreateChild')" @closed="drawerClosed('drawerCreateChild')">
            <crm-create ref="drawerCreateChild" drawer="drawerCreate"></crm-create>
        </el-drawer>
        <el-drawer :with-header="false" :visible.sync="drawerUpdate" size="60%" ref="drawerUpdate"
            @opened="drawerOpened('drawerUpdateChild')" @closed="drawerClosed('drawerUpdateChild')">
            <crm-update :founder="selectedItem" ref="drawerUpdateChild" drawer="drawerUpdate"></crm-update>
        </el-drawer>
    </div>
</template>
<script>
import { mapGetters, mapActions, mapState } from "vuex";
import CrmCreate from "./components/crm-create";
import CrmUpdate from "./components/crm-update";
import pricePr from "./components/pricePr"
import list from "@/utils/mixins/list";
import fixed from "@/utils/mixins/fixed";
import users from '@inventory/crm-user-select';
import { i18n } from '@/utils/modules/i18n';
import { formatMoney } from '@/filters';

export default {
    mixins: [list, fixed],
    components: { CrmCreate, CrmUpdate, users, pricePr },
    data() {
        return {
            visibleSharePercentages: false,
            loadingSharePercentages: false,
            share_percentages: [],
        };
    },
    computed: {
        ...mapState({
            defaultColumns: state => state.founders.defaultColumns
        }),
        ...mapGetters({
            list: 'founders/list',
            charter_capital: 'founders/charter_capital',
            mode: "MODE",
            columns: "founders/columns",
            pagination: "founders/pagination",
            filter: "founders/filter",
            sort: "founders/sort",
        }),
        actions: function () {
            return ['edit', 'delete'];
        },
        dataTableHeader() {
            return Object.keys(this.columns)
        }
    },
    methods: {
        ...mapActions({
            updateList: 'founders/index',
            updateSort: "founders/updateSort",
            updateFilter: "founders/updateFilter",
            updateColumn: "founders/updateColumn",
            updatePagination: "founders/updatePagination",
            empty: 'founders/empty',
            delete: 'founders/destroy',
            refreshData: 'founders/refreshData',
            updateInventoryList: 'founders/inventory',
            getSharePercentageOfFounder: 'founders/getSharePercentageOfFounder',
        }),
        showSharePercentages(model) {
            this.visibleSharePercentages = true;
            this.loadingSharePercentages = true;

            this.getSharePercentageOfFounder(model.id)
                .then((res) => {
                    this.share_percentages = res ? res.operations : [];
                    this.loadingSharePercentages = false;
                })
                .catch((err) => {
                    this.loadingSharePercentages = false;
                    this.$alert(err);
                });
        },
        emptySharePercentages() {
            this.share_percentages = [];
        },
        controlExcelData() {
            this.excel_fields = {};
            for (let key in this.columns) {
                if (this.columns.hasOwnProperty(key)) {
                    let column = this.columns[key];
                    if (column.show && column.column !== 'settings') {
                        switch (column.column) {
                            case 'user_id':
                                this.excel_fields[column.title] = 'user.name'; break;
                            case 'total_price':
                                this.excel_fields[column.title] = {
                                    field: 'total_price',
                                    callback: (value) => {
                                        return formatMoney(value);
                                    }
                                };
                                break;
                            case 'share_percentage':
                                this.excel_fields[column.title] = {
                                    field: 'share_percentage',
                                    callback: (value) => {
                                        return value + '%';
                                    }
                                };
                                break;
                            case 'is_active':
                                this.excel_fields[column.title] = {
                                    field: 'is_active',
                                    callback: (value) => {
                                        return value ? i18n.t('message.yes') : i18n.t('message.no');
                                    }
                                };
                                break;
                            case 'phone':
                                this.excel_fields[column.title] = 'user.phone'; break;
                            case 'email':
                                this.excel_fields[column.title] = 'user.email'; break;
                            default:
                                this.excel_fields[column.title] = column.column; break;
                        }
                    }
                }
            }
        },
    },
    beforeRouteLeave(to, from, next) {
        this.$store.commit('founders/EMPTY_LIST');
        next()
    },
};
</script>
