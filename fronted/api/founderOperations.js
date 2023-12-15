import request from '@/utils/request'

export function index(params) {
    return request({
        url: '/founderOperations',
        method: 'get',
        params
    })
}
export function show(id) {
    return request({
        url: `/founderOperations/${id}`,
        method: 'get'
    })
}

export function store(data) {
    return request({
        url: '/founderOperations',
        method: 'post',
        data
    })
}

export function update(data) {
    return request({
        url: `/founderOperations/${data.id}`,
        method: 'put',
        data
    })
}

export function destroy(id) {
    return request({
        url: `/founderOperations/${id}`,
        method: 'delete',
    })
}

export function getOperationTypes() {
    return request({
        url: '/founderOperations/getOperationTypes',
        method: 'get'
    })
}

export function getInvestmentTypes() {
    return request({
        url: '/founderOperations/getInvestmentTypes',
        method: 'get'
    })
}

export function getOutputTypes() {
    return request({
        url: '/founderOperations/getOutputTypes',
        method: 'get'
    })
}

export function getInvestmentHistoryOfFounder(founder_id) {
    return request({
        url: `/founderOperations/getInvestmentHistoryOfFounder/${founder_id}`,
        method: 'get'
    })
}

export function deleteNomenclature(data) {
    return request({
        url: '/founderOperations/deleteNomenclature',
        method: 'post',
        data
    })
}

export function getNomenclatures(operation_id) {
    return request({
        url: `/founderOperations/getNomenclatures/${operation_id}`,
        method: 'get'
    })
}
