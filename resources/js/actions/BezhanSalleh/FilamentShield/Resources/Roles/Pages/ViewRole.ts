import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
const ViewRole = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ViewRole.url(args, options),
    method: 'get',
})

ViewRole.definition = {
    methods: ["get","head"],
    url: '/admin/shield/roles/{record}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
ViewRole.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { record: args }
    }

    if (Array.isArray(args)) {
        args = {
            record: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        record: args.record,
    }

    return ViewRole.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
ViewRole.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ViewRole.url(args, options),
    method: 'get',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
ViewRole.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ViewRole.url(args, options),
    method: 'head',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
const ViewRoleForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewRole.url(args, options),
    method: 'get',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
ViewRoleForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewRole.url(args, options),
    method: 'get',
})

/**
* @see \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::__invoke
* @see vendor/bezhansalleh/filament-shield/src/Resources/Roles/Pages/ViewRole.php:7
* @route '/admin/shield/roles/{record}'
*/
ViewRoleForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewRole.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ViewRole.form = ViewRoleForm

export default ViewRole