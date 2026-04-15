import Actions from './Actions'
import Auth from './Auth'
import Pages from './Pages'
import Http from './Http'

const Filament = {
    Actions: Object.assign(Actions, Actions),
    Auth: Object.assign(Auth, Auth),
    Pages: Object.assign(Pages, Pages),
    Http: Object.assign(Http, Http),
}

export default Filament