import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
const CreateRole = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateRole.url(options),
    method: 'get',
})

CreateRole.definition = {
    methods: ["get","head"],
    url: '/admin/shield/roles/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
CreateRole.url = (options?: RouteQueryOptions) => {
    return CreateRole.definition.url + queryParams(options)
}

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
CreateRole.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: CreateRole.url(options),
    method: 'get',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
CreateRole.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: CreateRole.url(options),
    method: 'head',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
const CreateRoleForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateRole.url(options),
    method: 'get',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
CreateRoleForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateRole.url(options),
    method: 'get',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/CreateRole.php:7
* @route '/admin/shield/roles/create'
*/
CreateRoleForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: CreateRole.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

CreateRole.form = CreateRoleForm

export default CreateRole