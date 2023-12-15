<template>
    <div>
        <header id="el-drawer__title" class="el-drawer__header">
            <span>
                {{ $t("message.nomenclatures") }}
            </span>
            <el-button
                type="warning"
                @click="close()"
                icon="el-icon-close"
                size="small"
            >
                {{ $t("message.close") }}</el-button
            >
        </header>
        <el-main>
            <el-card class="box-card pb-2 p-1">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.products') }}</span>
                </div>
                <el-table size="medium" :data="investment_item.products_nomenclature" style="width: 100%" class="crm-el-table">
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
                            {{ item.row.quantity | formatNumber }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.measurement')" width="150px">
                        <template slot-scope="item">
                            {{ item.row.product ? item.row.product.measurement ? item.row.product.measurement.name : '' : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.price')" width="230px">
                        <template slot-scope="item">
                            {{ item.row.price | formatNumber }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.currency')">
                        <template slot-scope="item">
                            {{ item.row.currency ? item.row.currency.name : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.rate')">
                        <template slot-scope="item">
                            <template v-if="!(item.row.currency && item.row.currency.active)">
                                {{ item.row.rate | formatNumber }}
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.summa')">
                        <template slot-scope="item">
                            {{ (item.row.quantity * item.row.price) | formatNumber }} {{ item.row.currency ? item.row.currency.symbol : ''}}
                        </template>
                    </el-table-column>
                </el-table>
            </el-card>

            <el-card class="box-card pb-2 p-1 mt-3">
                <div slot="header" class="clearfix">
                    <span>{{ $t('message.materials') }}</span>
                </div>
                <el-table size="medium" :data="investment_item.materials_nomenclature" style="width: 100%" class="crm-el-table">
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
                            {{ item.row.quantity | formatNumber }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.measurement')" width="150px">
                        <template slot-scope="item">
                            {{ item.row.material ? item.row.material.measurement ? item.row.material.measurement.name : '' : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.price')"  width="230px">
                        <template slot-scope="item">
                            {{ item.row.price | formatNumber }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.currency')">
                        <template slot-scope="item">
                            {{ item.row.currency ? item.row.currency.name : '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.rate')">
                        <template slot-scope="item">
                            <template v-if="!(item.row.currency && item.row.currency.active)">
                                {{ item.row.rate | formatNumber }}
                            </template>
                        </template>
                    </el-table-column>
                    <el-table-column :label="$t('message.summa')">
                        <template slot-scope="item">
                            {{ (item.row.quantity * item.row.price) | formatNumber }} {{ item.row.currency ? item.row.currency.symbol : ''}}
                        </template>
                    </el-table-column>
                </el-table>
            </el-card>
        </el-main>
    </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import drawer from "@/utils/mixins/includes/drawer";

export default {
    props: ['investment_item'],
    mixins: [drawer],
    data() {
        return {

        };
    },
    methods: {
        ...mapActions({

        }),
        afterOpen() {

        },
    }
};
</script>
