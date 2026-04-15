import exports from './exports'
import imports from './imports'
import admin from './admin'
import development from './development'

const filament = {
    exports: Object.assign(exports, exports),
    imports: Object.assign(imports, imports),
    admin: Object.assign(admin, admin),
    development: Object.assign(development, development),
}

export default filament