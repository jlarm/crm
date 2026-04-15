import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
const EditProfile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditProfile.url(options),
    method: 'get',
})

EditProfile.definition = {
    methods: ["get","head"],
    url: '/admin/edit-profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
EditProfile.url = (options?: RouteQueryOptions) => {
    return EditProfile.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
EditProfile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: EditProfile.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
EditProfile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: EditProfile.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
const EditProfileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditProfile.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
EditProfileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditProfile.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Pages\EditProfile::__invoke
* @see app/Filament/pages/EditProfile.php:7
* @route '/admin/edit-profile'
*/
EditProfileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: EditProfile.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

EditProfile.form = EditProfileForm

export default EditProfile