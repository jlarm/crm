import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/email-tracking-events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
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
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
export const view = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: view.url(args, options),
    method: 'get',
})

view.definition = {
    methods: ["get","head"],
    url: '/admin/email-tracking-events/{record}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
view.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return view.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
view.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: view.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
view.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: view.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
const viewForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: view.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
viewForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: view.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
viewForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: view.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

view.form = viewForm

const emailTrackingEvents = {
    index: Object.assign(index, index),
    view: Object.assign(view, view),
}

export default emailTrackingEvents