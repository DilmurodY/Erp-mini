<template>
    <div>
        <header id="el-drawer__title" class="el-drawer__header">
            <span>
                {{ $t("message.edit") }} {{ $t("message.operation") | lowerFirst }}
            </span>
            <el-button
                v-can="['founderOperations.update']"
                type="primary"
                size="small"
                class="mr-1"
                :loading="waiting"
                @click="submit()"
            >
                {{ $t("message.save_and_exit") }}</el-button
            >
            <el-button
                type="warning"
                @click="close()"
                icon="el-icon-close"
                size="small"
            >
                {{ $t("message.close") }}</el-button
            >
        </header>
        <el-main class="pt-3" v-loading="loading">
            <el-card class="box-card crm-card-pt-1">
                <el-form
                    ref="form"
                    status-icon
                    :model="form"
                    :rules="rules"
                    label-position="top" 
                    class="khan_form-style error"
                    size="small"
                >
                    <el-row :gutter="24">
                        <el-col :span="8">
                            <founders v-model="form.founder_id" :founder_id="form.founder_id"></founders>
                            <el-form-item prop="start_date" class="d-flex">
                                <label class="el-form-item__label">{{ columns.start_date.title }}</label>
                                <el-date-picker v-model="form.start_date" prefix-icon="el-icon-date" type="datetime" :format="date_time_format" :value-format="date_time_format">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="columns.operation_type_id.title" prop="operation_type_id">
                                <el-select v-model="form.operation_type_id" :placeholder="columns.operation_type_id.title">
                                    <el-option v-for="(operation_type, index) in operationTypes" :key="index + 'operation_types'" :label="operation_type.name" :value="operation_type.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item v-if="form.operation_type_id == 1" :label="columns.investment_type_id.title">
                                <el-select v-model="form.investment_type_id" clearable :placeholder="columns.investment_type_id.title">
                                    <el-option v-for="(investment_type, index) in investmentTypes" :key="index + 'investment_types'" :label="investment_type.name" :value="investment_type.id"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item v-else-if="form.operation_type_id == 2" :label="columns.output_type_id.title">
                                <el-select v-model="form.output_type_id" clearable :placeholder="columns.output_type_id.title">
                                    <el-option v-for="(output_type, index) in outputTypes" :key="index + 'output_types'" :label="output_type.name" :value="output_type.id"></el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="columns.total_price.title" prop="total_price" size="small">
                                <amount v-model="form.total_price" :old="form.total_price" :is_disabled="is_disable_total_price_and_currency" size="small"></amount>
                            </el-form-item>
                            <el-form-item :label="columns.currency_id.title" prop="currency_id"  size="small">
                                <currencies v-model="form.currency_id" :currency_id="form.currency_id" :is_disabled="is_disable_total_price_and_currency" @c-change="updateCurrency()"></currencies>
                            </el-form-item>
                            <el-form-item v-if="form.currency && !form.currency.active" :disabled="is_disable_total_price_and_currency" :label="columns.rate.title">
                                <el-input v-model="form.rate" type="number"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
            </el-card>

            <el-card v-if="form.operation_type_id == 1 && form.investment_type_id == 2" class="box-card pb-2 p-1 mt-3">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.products') }}</span>
                    <h4 style="float: right;" class="font-weight-bold">{{ totalProductsAmount | formatMoney(2) }}</h4>
                </div>
                <el-table size="medium" :data="[...old_product_items, ...product_items]" style="width: 100%" class="crm-el-table">
                    <template slot="empty">
                        <span></span>
                    </template>
                    <el-table-column :label="$t('message.name')" width="300px">
                        <template slot-scope="item">
                            <b>{{ (item.row.product ? item.row.product.name : '') }}</b>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.quantity')" width="150px">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.quantity | formatNumber }}
                            </template>
                            <template v-else>
                                <el-input type="number" v-model="item.row.quantity" :min="0" size="mini"></el-input>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.measurement')" width="150px">
                        <template slot-scope="item">
                            {{ item.row.product ? item.row.product.measurement ? item.row.product.measurement.name : '' : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.price')" width="230px">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.price | formatNumber }}
                            </template>
                            <template v-else>
                                <product-price v-model="item.row.price" :old="item.row.price" size="mini"></product-price>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.currency')">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.currency ? item.row.currency.symbol : '' }}
                            </template>
                            <template v-else>
                                <currencies size="mini" v-model="item.row.currency_id" :currency_id="item.row.currency_id" @c-change="updateCurrencyItem(item.row)"></currencies>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.rate')">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.rate | formatNumber }}
                            </template>
                            <template v-else>
                                <el-input v-model="item.row.rate" :hidden="item.row.currency && item.row.currency.active" type="number" size="mini"></el-input>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.summa')">
                        <template slot-scope="item">
                            {{ (item.row.quantity * item.row.price) | formatNumber }} {{ item.row.currency ? item.row.currency.symbol : ''}}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.delete')" width="80px">
                        <template slot-scope="item">
                            <el-button @click="item.row.id ? deleteOldNomenclature(item.row.id) : removeProduct(item.row)" type="danger" icon="el-icon-delete" size="mini" circle></el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <el-col :span="12" class="mt-2">
                    <products @append="appendProduct" :plc="$t('message.product_select_plc')"></products>
                </el-col>
            </el-card>

            <el-card v-if="form.operation_type_id == 1 && form.investment_type_id == 2" class="box-card pb-2 p-1 mt-3">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.materials') }}</span>
                    <h4 style="float: right;" class="font-weight-bold">{{ totalMaterialsAmount | formatMoney(2) }}</h4>
                </div>
                <el-table size="medium" :data="[...old_material_items, ...material_items]" style="width: 100%" class="crm-el-table">
                    <template slot="empty">
                        <span></span>
                    </template>
                    <el-table-column :label="$t('message.name')" width="300px">
                        <template slot-scope="item">
                            <b>{{ (item.row.material ? item.row.material.name : '') }}</b>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.quantity')" width="150px">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.quantity | formatNumber }}
                            </template>
                            <template v-else>
                                <el-input type="number" v-model="item.row.quantity" :min="0" size="mini"></el-input>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.measurement')" width="150px">
                        <template slot-scope="item">
                            {{ item.row.material ? item.row.material.measurement ? item.row.material.measurement.name : '' : '' }}
                            {{ item.row.material | addMeasurement(item.row.quantity)}}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.price')"  width="230px">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.price | formatNumber }}
                            </template>
                            <template v-else>
                                <material-price v-model="item.row.price" :old="item.row.price" size="mini"></material-price>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.currency')">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.currency ? item.row.currency.symbol : '' }}
                            </template>
                            <template v-else>
                                <currencies size="mini" v-model="item.row.currency_id" :currency_id="item.row.currency_id" @c-change="updateCurrencyItem(item.row)"></currencies>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.rate')">
                        <template slot-scope="item">
                            <template v-if="item.row.id">
                                {{ item.row.rate | formatNumber }}
                            </template>
                            <template v-else>
                                <el-input :hidden="item.row.currency && item.row.currency.active" type="number" v-model="item.row.rate" size="mini"></el-input>
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.summa')">
                        <template slot-scope="item">
                            {{ (item.row.quantity * item.row.price) | formatNumber }} {{ item.row.currency ? item.row.currency.symbol : ''}}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.delete')" width="80px">
                        <template slot-scope="item">
                            <el-button @click="item.row.id ? deleteOldNomenclature(item.row.id) : removeMaterial(item.row)" type="danger" icon="el-icon-delete" size="mini" circle></el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <el-col :span="12" class="mt-1">
                    <materials @append="appendMaterial" :plc="$t('message.product_select_plc')"></materials>
                </el-col>
            </el-card>

            <!-- Istoriya vlojeniya -->
            <el-card v-if="form.operation_type_id == 2" class="box-card pb-2 p-1 mt-3">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.investment_history') }}</span>
                    <h4 style="float: right;" class="font-weight-bold">{{ share_percentage_present_time | formatNumber(2) }} %</h4>
                </div>
                <el-table size="medium" :data="investment_histories" style="width: 100%" class="crm-el-table">
                    <template slot="empty">
                        <span></span>
                    </template>
                    <el-table-column :label="$t('message.investment_type')" width="300px">
                        <template slot-scope="item">
                            {{ (item.row.investment_type ? item.row.investment_type.name : '') }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.Begin date')">
                        <template slot-scope="item">
                            {{ item.row.start_date }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.Amount')">
                        <template slot-scope="item">
                            {{ item.row.total_price | formatNumber(2) }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.currency')">
                        <template slot-scope="item">
                            {{ item.row.currency ? item.row.currency.name : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.rate')">
                        <template slot-scope="item">
                            {{ item.row.rate | formatNumber(2) }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.share_percentage')">
                        <template slot-scope="item">
                            {{ item.row.current_share_percentage | formatNumber(2) }} %
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.nomenclatures')">
                        <template slot-scope="item">
                            <template v-if="item.row.investment_type_id == 2">
                                <el-button @click="showNomenclatures(item.row)" type="primary" icon="el-icon-view" size="mini" plain></el-button>
                            </template>
                        </template>
                    </el-table-column>
                </el-table>
            </el-card>
        </el-main>

        <el-drawer :with-header="false" :visible.sync="drawerShowNomenclatures" size="90%" ref="drawerShowNomenclatures" append-to-body @opened="drawerOpened('drawerShowNomenclaturesChild')" @closed="drawerClosed('drawerShowNomenclaturesChild')">
            <crm-nomenclatures drawer="drawerShowNomenclatures" :investment_item="selectedInvestmentItem" ref="drawerShowNomenclaturesChild"></crm-nomenclatures>
        </el-drawer>
    </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import drawer from "@/utils/mixins/includes/drawer";
import form from "@/utils/mixins/form";
import founderOperation from "@/utils/mixins/models/founder/founderOperation";

export default {
    props: ["founderOperation"],
    mixins: [drawer, form, founderOperation],
    data() {
        return {
            is_edit_form: true,
        };
    },
    methods: {
        ...mapActions({
            save: "founderOperations/update",
            getModel: "founderOperations/show",
            deleteNomenclature: 'founderOperations/deleteNomenclature',
        }),
        afterOpen() {
            this.form = this.getForm;
            this.load();
        },
        load() {
            if (!this.loading && this.founderOperation) {
                this.changeLoading(true);
                this.getModel(this.founderOperation.id)
                    .then(res => {
                        this.form = this.getForm;
                        this.changeLoading();
                    })
                    .catch(err => {
                        this.changeLoading();
                    });
            }
        },
        deleteOldNomenclature(id){
            this.$confirm(this.$t('message.delete_confirm'), this.$t('message.confirm'), {
                confirmButtonText: this.$t('message.yes'),
                cancelButtonText: this.$t('message.cancel'),
                type: 'warning'
            }).then(() => {
                this.deleteNomenclature({operation_nomenclature_id: id})
                    .then(res=> {
                        this.load();
                        this.listChanged();
                        this.$alert(res);
                    })
                    .catch(err => {
                        this.$alert(err)
                    })
            }).catch(() => {});
        },
    }
};
</script>
