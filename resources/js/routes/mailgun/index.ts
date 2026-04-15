import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\MailgunWebhookController::webhook
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
export const webhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ["post"],
    url: '/webhooks/mailgun',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\MailgunWebhookController::webhook
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
webhook.url = (options?: RouteQueryOptions) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\MailgunWebhookController::webhook
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
webhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: webhook.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::webhook
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
const webhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: webhook.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::webhook
* @see app/Http/Controllers/MailgunWebhookController.php:18
* @route '/webhooks/mailgun'
*/
webhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: webhook.url(options),
    method: 'post',
})

webhook.form = webhookForm

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
export const openTrack = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openTrack.url(args, options),
    method: 'get',
})

openTrack.definition = {
    methods: ["get","head"],
    url: '/track/open/{message_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
openTrack.url = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return openTrack.definition.url
            .replace('{message_id}', parsedArgs.message_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
openTrack.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openTrack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
openTrack.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openTrack.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
const openTrackForm = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: openTrack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
openTrackForm.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: openTrack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::openTrack
* @see app/Http/Controllers/MailgunWebhookController.php:49
* @route '/track/open/{message_id}'
*/
openTrackForm.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: openTrack.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

openTrack.form = openTrackForm

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
export const clickTrack = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: clickTrack.url(args, options),
    method: 'get',
})

clickTrack.definition = {
    methods: ["get","head"],
    url: '/track/click/{message_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
clickTrack.url = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return clickTrack.definition.url
            .replace('{message_id}', parsedArgs.message_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
clickTrack.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: clickTrack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
clickTrack.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: clickTrack.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
const clickTrackForm = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: clickTrack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
clickTrackForm.get = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: clickTrack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\MailgunWebhookController::clickTrack
* @see app/Http/Controllers/MailgunWebhookController.php:102
* @route '/track/click/{message_id}'
*/
clickTrackForm.head = (args: { message_id: string | number } | [message_id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: clickTrack.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

clickTrack.form = clickTrackForm

const mailgun = {
    webhook: Object.assign(webhook, webhook),
    openTrack: Object.assign(openTrack, openTrack),
    clickTrack: Object.assign(clickTrack, clickTrack),
}

export default mailgun