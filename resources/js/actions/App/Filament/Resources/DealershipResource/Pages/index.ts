import ListDealerships from './ListDealerships'
import CreateDealership from './CreateDealership'
import EditDealership from './EditDealership'
import ManageDealershipStores from './ManageDealershipStores'
import ManageDealershipContacts from './ManageDealershipContacts'
import ManageDealershipProgresses from './ManageDealershipProgresses'
import ManageDealershipDealerEmails from './ManageDealershipDealerEmails'

const Pages = {
    ListDealerships: Object.assign(ListDealerships, ListDealerships),
    CreateDealership: Object.assign(CreateDealership, CreateDealership),
    EditDealership: Object.assign(EditDealership, EditDealership),
    ManageDealershipStores: Object.assign(ManageDealershipStores, ManageDealershipStores),
    ManageDealershipContacts: Object.assign(ManageDealershipContacts, ManageDealershipContacts),
    ManageDealershipProgresses: Object.assign(ManageDealershipProgresses, ManageDealershipProgresses),
    ManageDealershipDealerEmails: Object.assign(ManageDealershipDealerEmails, ManageDealershipDealerEmails),
}

export default Pages