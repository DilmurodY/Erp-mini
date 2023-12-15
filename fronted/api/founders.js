import request from '@/utils/request'

export function index(params) {
    return request({
        url: '/founders',
        method: 'get',
        params
    })
}
export function inventory(params) {
    return request({
        url: '/founders/inventory',
        method: 'get',
        params
    })
}
export function show(id) {
    return request({
        url: `/founders/${id}`,
        method: 'get'
    })
}

export function store(data) {
    return request({
        url: '/founders',
        method: 'post',
        data
    })
}

export function update(data) {
    return request({
        url: `/founders/${data.id}`,
        method: 'put',
        data
    })
}

export function destroy(id) {
    return request({
        url: `/founders/${id}`,
        method: 'delete',
    })
}

export function getSharePercentageOfFounder(founder_id) {
    return request({
        url: `/founders/getSharePercentageOfFounder/${founder_id}`,
        method: 'get'
    })
}
