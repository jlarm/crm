import ListRoles from './ListRoles'
import CreateRole from './CreateRole'
import ViewRole from './ViewRole'
import EditRole from './EditRole'

const Pages = {
    ListRoles: Object.assign(ListRoles, ListRoles),
    CreateRole: Object.assign(CreateRole, CreateRole),
    ViewRole: Object.assign(ViewRole, ViewRole),
    EditRole: Object.assign(EditRole, EditRole),
}

export default Pages