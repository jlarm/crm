import profile from './profile'
import security from './security'
import appearance from './appearance'

const settings = {
    profile: Object.assign(profile, profile),
    security: Object.assign(security, security),
    appearance: Object.assign(appearance, appearance),
}

export default settings