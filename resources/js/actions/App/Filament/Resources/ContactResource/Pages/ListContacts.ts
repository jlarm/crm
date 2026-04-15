import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
const ListContacts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListContacts.url(options),
    method: 'get',
})

ListContacts.definition = {
    methods: ["get","head"],
    url: '/admin/contacts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
ListContacts.url = (options?: RouteQueryOptions) => {
    return ListContacts.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
ListContacts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListContacts.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
ListContacts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListContacts.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
const ListContactsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListContacts.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
ListContactsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListContacts.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\ContactResource\Pages\ListContacts::__invoke
* @see app/Filament/Resources/ContactResource/Pages/ListContacts.php:7
* @route '/admin/contacts'
*/
ListContactsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListContacts.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListContacts.form = ListContactsForm

export default ListContacts