<template>
    <el-col :span="24">
        <header id="el-drawer__title" class="el-drawer__header">
            <span>
                {{ $t("message.edit") }} {{ $t("message.founder") | lowerFirst }}
            </span>
            <el-button
                v-can="['founders.update']"
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
            <el-card class="box-card pb-1 crm-card-pt-1">
                <el-form
                    ref="form"
                    status-icon
                    :model="form"
                    :rules="rules"
                    label-position="top" 
                    class="khan_form-style error"
                    size="medium"
                >
                    <el-row :gutter="24">
                        <el-col :span="12">
                            <users v-model="form.user_id" :user_id="form.user_id"></users>
                            <el-form-item :label="columns.is_active.title">
                                <el-checkbox
                                    class="checkbox_f_khan"
                                    v-model="form.is_active"
                                ></el-checkbox>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item
                                :label="columns.comment.title"
                                size="small"
                                prop="comment"
                            >
                                <el-input
                                    type="textarea"
                                    size="mini"
                                    v-model="form.comment"
                                    :placeholder="columns.comment.title"
                                    clearable
                                ></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
            </el-card>
        </el-main>
    </el-col>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import drawer from "@/utils/mixins/includes/drawer";
import form from "@/utils/mixins/form";
import founder from "@/utils/mixins/models/founder/founder";

export default {
    props: ["founder"],
    mixins: [drawer, form, founder],
    data() {
        return {

        };
    },
    methods: {
        ...mapActions({
            save: "founders/update",
            getModel: "founders/show"
        }),
        afterOpen() {
            this.form = this.getForm;
            this.load();
        },
        load() {
            if (!this.loading && this.founder) {
                this.changeLoading(true);
                this.getModel(this.founder.id)
                    .then(res => {
                        this.form = this.getForm;
                        this.changeLoading();
                    })
                    .catch(err => {
                        this.changeLoading();
                    });
            }
        }
    }
};
</script>
