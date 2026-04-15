import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/contacts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const contacts = {
    index: Object.assign(index, index),
}

export default contacts