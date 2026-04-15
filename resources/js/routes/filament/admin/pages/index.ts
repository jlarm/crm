import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
export const editProfile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editProfile.url(options),
    method: 'get',
})

editProfile.definition = {
    methods: ["get","head"],
    url: '/admin/edit-profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
editProfile.url = (options?: RouteQueryOptions) => {
    return editProfile.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
editProfile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editProfile.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
editProfile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editProfile.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
const editProfileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: editProfile.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
editProfileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: editProfile.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
editProfileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: editProfile.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

editProfile.form = editProfileForm

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/admin',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \Filament\Pages\Dashboard::__invoke
* @see vendor/filament/filament/src/Pages/Dashboard.php:7
* @route '/admin'
*/
dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

dashboard.form = dashboardForm

const pages = {
    editProfile: Object.assign(editProfile, editProfile),
    dashboard: Object.assign(dashboard, dashboard),
}

export default pages