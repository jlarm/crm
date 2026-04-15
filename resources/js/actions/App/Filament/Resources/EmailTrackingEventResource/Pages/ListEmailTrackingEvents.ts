import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
const ListEmailTrackingEvents = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListEmailTrackingEvents.url(options),
    method: 'get',
})

ListEmailTrackingEvents.definition = {
    methods: ["get","head"],
    url: '/admin/email-tracking-events',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
ListEmailTrackingEvents.url = (options?: RouteQueryOptions) => {
    return ListEmailTrackingEvents.definition.url + queryParams(options)
}

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
ListEmailTrackingEvents.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ListEmailTrackingEvents.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
ListEmailTrackingEvents.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ListEmailTrackingEvents.url(options),
    method: 'head',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
const ListEmailTrackingEventsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListEmailTrackingEvents.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
ListEmailTrackingEventsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListEmailTrackingEvents.url(options),
    method: 'get',
})

/**
* @see \App\Filament\Resources\EmailTrackingEventResource\Pages\ListEmailTrackingEvents::__invoke
* @see app/Filament/Resources/EmailTrackingEventResource/Pages/ListEmailTrackingEvents.php:7
* @route '/admin/email-tracking-events'
*/
ListEmailTrackingEventsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ListEmailTrackingEvents.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ListEmailTrackingEvents.form = ListEmailTrackingEventsForm

export default ListEmailTrackingEvents