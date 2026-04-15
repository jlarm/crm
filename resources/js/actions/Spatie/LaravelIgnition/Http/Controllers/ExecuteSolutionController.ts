import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/ExecuteSolutionController.php:15
* @route '/_ignition/execute-solution'
*/
const ExecuteSolutionController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ExecuteSolutionController.url(options),
    method: 'post',
})

ExecuteSolutionController.definition = {
    methods: ["post"],
    url: '/_ignition/execute-solution',
} satisfies RouteDefinition<["post"]>

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/ExecuteSolutionController.php:15
* @route '/_ignition/execute-solution'
*/
ExecuteSolutionController.url = (options?: RouteQueryOptions) => {
    return ExecuteSolutionController.definition.url + queryParams(options)
}

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/ExecuteSolutionController.php:15
* @route '/_ignition/execute-solution'
*/
ExecuteSolutionController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: ExecuteSolutionController.url(options),
    method: 'post',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/ExecuteSolutionController.php:15
* @route '/_ignition/execute-solution'
*/
const ExecuteSolutionControllerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: ExecuteSolutionController.url(options),
    method: 'post',
})

/**
* @see \Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController::__invoke
* @see vendor/spatie/laravel-ignition/src/Http/Controllers/ExecuteSolutionController.php:15
* @route '/_ignition/execute-solution'
*/
ExecuteSolutionControllerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: ExecuteSolutionController.url(options),
    method: 'post',
})

ExecuteSolutionController.form = ExecuteSolutionControllerForm

export default ExecuteSolutionController