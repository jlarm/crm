import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
export const view = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: view.url(args, options),
    method: 'get',
})

view.definition = {
    methods: ["get","head"],
    url: '/pdf/{attachment}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
view.url = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { attachment: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { attachment: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            attachment: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        attachment: typeof args.attachment === 'object'
        ? args.attachment.id
        : args.attachment,
    }

    return view.definition.url
            .replace('{attachment}', parsedArgs.attachment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
view.get = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: view.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
view.head = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: view.url(args, options),
    method: 'head',
})

/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
const viewForm = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: view.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
viewForm.get = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: view.url(args, options),
    method: 'get',
})

/**
* @see routes/web.php:41
* @route '/pdf/{attachment}'
*/
viewForm.head = (args: { attachment: number | { id: number } } | [attachment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: view.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

view.form = viewForm

const pdf = {
    view: Object.assign(view, view),
}

export default pdf