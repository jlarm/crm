import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ListDealerships::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ListDealerships.php:7
* @route '/admin/dealerships'
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

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\CreateDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/CreateDealership.php:7
* @route '/admin/dealerships/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
export const edit = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
edit.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
edit.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
edit.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
const editForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
editForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\EditDealership::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/EditDealership.php:7
* @route '/admin/dealerships/{record}/edit'
*/
editForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
export const stores = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stores.url(args, options),
    method: 'get',
})

stores.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/stores',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
stores.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return stores.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
stores.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: stores.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
stores.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: stores.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
const storesForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: stores.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
storesForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: stores.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipStores::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipStores.php:7
* @route '/admin/dealerships/{record}/stores'
*/
storesForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: stores.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

stores.form = storesForm

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
export const contacts = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: contacts.url(args, options),
    method: 'get',
})

contacts.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/contacts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
contacts.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return contacts.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
contacts.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: contacts.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
contacts.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: contacts.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
const contactsForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: contacts.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
contactsForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: contacts.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipContacts::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipContacts.php:7
* @route '/admin/dealerships/{record}/contacts'
*/
contactsForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: contacts.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

contacts.form = contactsForm

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
export const progresses = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progresses.url(args, options),
    method: 'get',
})

progresses.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/progresses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
progresses.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return progresses.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
progresses.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: progresses.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
progresses.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: progresses.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
const progressesForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: progresses.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
progressesForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: progresses.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipProgresses::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipProgresses.php:7
* @route '/admin/dealerships/{record}/progresses'
*/
progressesForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: progresses.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

progresses.form = progressesForm

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
export const emails = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: emails.url(args, options),
    method: 'get',
})

emails.definition = {
    methods: ["get","head"],
    url: '/admin/dealerships/{record}/emails',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
emails.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return emails.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
emails.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: emails.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
emails.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: emails.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
const emailsForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: emails.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
emailsForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: emails.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\DealershipResource\Pages\ManageDealershipDealerEmails::__invoke
* @see app/Filament/Resources/DealershipResource/Pages/ManageDealershipDealerEmails.php:7
* @route '/admin/dealerships/{record}/emails'
*/
emailsForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: emails.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

emails.form = emailsForm

const dealerships = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    edit: Object.assign(edit, edit),
    stores: Object.assign(stores, stores),
    contacts: Object.assign(contacts, contacts),
    progresses: Object.assign(progresses, progresses),
    emails: Object.assign(emails, emails),
}

export default dealerships