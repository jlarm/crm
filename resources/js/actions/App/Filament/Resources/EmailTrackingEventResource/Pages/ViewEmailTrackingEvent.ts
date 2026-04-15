import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
const ViewEmailTrackingEvent = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ViewEmailTrackingEvent.url(args, options),
    method: 'get',
})

ViewEmailTrackingEvent.definition = {
    methods: ["get","head"],
    url: '/admin/email-tracking-events/{record}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
ViewEmailTrackingEvent.url = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return ViewEmailTrackingEvent.definition.url
            .replace('{record}', parsedArgs.record.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
ViewEmailTrackingEvent.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ViewEmailTrackingEvent.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
ViewEmailTrackingEvent.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ViewEmailTrackingEvent.url(args, options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
const ViewEmailTrackingEventForm = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewEmailTrackingEvent.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
ViewEmailTrackingEventForm.get = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewEmailTrackingEvent.url(args, options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ViewEmailTrackingEvent::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ViewEmailTrackingEvent.php:7
* @route '/admin/email-tracking-events/{record}'
*/
ViewEmailTrackingEventForm.head = (args: { record: string | number } | [record: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ViewEmailTrackingEvent.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ViewEmailTrackingEvent.form = ViewEmailTrackingEventForm

export default ViewEmailTrackingEvent