export default class Gate {
    constructor(user){
        this.user = user;
    }

    isAdmin(){
        return this.user.type === 'admin';
    }

    isUser(){
        return this.user.type === 'user';
    }

    isAdminOrAuthor(){
        return this.user.type === 'admin' || this.user.type === 'author';
    }

    isUserOrAuthor(){
        return this.user.type === 'user' || this.user.type === 'author';
    }
}