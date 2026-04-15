import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\MailgunWebhookController::handleEvent
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
export const handleEvent = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleEvent.url(options),
    method: 'post',
})

handleEvent.definition = {
    methods: ["post"],
    url: '/webhooks/mailgun',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\MailgunWebhookController::handleEvent
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
handleEvent.url = (options?: RouteQueryOptions) => {
    return handleEvent.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MailgunWebhookController::handleEvent
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
handleEvent.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: handleEvent.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::handleEvent
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
const handleEventForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: handleEvent.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::handleEvent
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
handleEventForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: handleEvent.url(options),
    method: 'post',
})

handleEvent.form = handleEventForm

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
export const trackOpen = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trackOpen.url(args, options),
    method: 'get',
})

trackOpen.definition = {
    methods: ["get","head"],
    url: '/track/open/{message_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
trackOpen.url = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { message_id: args }
    }

    if (Array.isArray(args)) {
        args = {
            message_id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        message_id: args.message_id,
    }

    return trackOpen.definition.url
            .replace('{message_id}', parsedArgs.message_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
trackOpen.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trackOpen.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
trackOpen.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: trackOpen.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
const trackOpenForm = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: trackOpen.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
trackOpenForm.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: trackOpen.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackOpen
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
trackOpenForm.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: trackOpen.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

trackOpen.form = trackOpenForm

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
export const trackClick = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trackClick.url(args, options),
    method: 'get',
})

trackClick.definition = {
    methods: ["get","head"],
    url: '/track/click/{message_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
trackClick.url = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { message_id: args }
    }

    if (Array.isArray(args)) {
        args = {
            message_id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        message_id: args.message_id,
    }

    return trackClick.definition.url
            .replace('{message_id}', parsedArgs.message_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
trackClick.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trackClick.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
trackClick.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: trackClick.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
const trackClickForm = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: trackClick.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
trackClickForm.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: trackClick.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::trackClick
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
trackClickForm.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: trackClick.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

trackClick.form = trackClickForm

const MailgunWebhookController = { handleEvent, trackOpen, trackClick }

export default MailgunWebhookController