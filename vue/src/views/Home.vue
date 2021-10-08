<template>
    <div>
        <header>
            <h2>YukPilih</h2>

            <div class="info">
                <h4>{{ me.username }}</h4> <span>|</span> <a href="" @click.prevent="logout">Logout</a>
            </div>
        </header>

        <main>
            <div class="container">
                <div class="poll" v-for="poll in polls" :key="poll.id">
                    <h2 class="title">{{ poll.title }}</h2>
                    <p class="detail">Created By: {{ poll.creator }} | Deadline: {{ poll.deadline }}</p>

                    <p class="description">{{ poll.description }}</p>

                    <div class="result" v-if="poll.result">
                        <div class="result-group" v-for="result in poll.result" :key="result.id">
                            <progress :value="result.point" :max="getMax(poll.result)"></progress>

                            <div class="res">
                                <div class="res-c">
                                    {{ result.choice }}
                                </div>
                                <div class="res-p">{{ getPercent(getMax(poll.result), result.point) }}%</div>
                            </div>
                        </div>
                    </div>

                    <div class="choices" v-if="!poll.result">
                        <div class="choice" v-for="choice in poll.choices" :key="choice.id">
                            <label :for="choice.id">{{ choice.choice }}</label>
                            <input type="radio" :name="poll.id" :id="choice.id" hidden @change="vote(poll.id, choice.id)">
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="modal" v-if="create">
            <form @submit.prevent="createPoll">
                <div class="form-group">
                    <label for="title">Title</label>	
                    <input type="text" id="title" v-model="formData.title">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>	
                    <input type="text" id="description" v-model="formData.description">
                </div>

                <div class="form-group">
                    <label for="deadline">Deadline</label>	
                    <input type="datetime-local" id="deadline" v-model="formData.deadline">
                </div>

                <div class="choice-group" v-for="(choice, i) in formData.choices" :key="i">
                    <input type="text" v-model="formData.choices[i]" :placeholder="`Choice ${i + 1}`" @keydown.once="addNewChoice">
                    <button type="button" class="button-rm danger" @click="removeChoice(i)">x</button>
                </div>


                <div class="button-group">
                    <button type="submit" class="primary">Create</button>
                    <button type="submit" class="danger" @click="create = false">Cancel</button>
                </div>
            </form>
        </div>

        <div class="alert" v-if="alert">{{ alert }}</div>
        <button v-if="me.role == 'admin'" class="fab" @click="create = true">+</button>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    data() {
        return {
            header: {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`
                }
            },
            formData: {
                title: '',
                description: '',
                deadline: '',
                choices: ['']
            },
            me: '',
            polls: '',
            alert: '',
            create: false
        }
    },
    methods: {
        getMe() {
            axios.post(`auth/me`, {}, this.header)
                .then(res => {
                    this.me = res.data
                    console.log(res.data);
                })
                .catch(err => {
                    this.alert = err.response.data.message;

                    setTimeout(() => {
                        this.alert = '';
                    }, 2000);
                }); 
        },
        logout() {
            axios.post(`auth/logout`, {}, this.header)
                .then(res => {
                    localStorage.removeItem('token');
                    this.$router.push('/');
                })
                .catch(err => {
                    this.alert = err.response.data.message;

                    setTimeout(() => {
                        this.alert = '';
                    }, 2000);
                }); 
        },
        getPoll() {
            axios.get(`poll`, this.header)
                .then(res => {
                    this.polls = res.data;
                    console.log(res.data);
                })
                .catch(err => {
                    this.alert = err.response.data.message;

                    setTimeout(() => {
                        this.alert = '';
                    }, 2000);
                }); 
        },
        getMax(poll) {
            let total = 0;

            poll.forEach(val => {
                total += val.point;
            });

            return total;
        },
        getPercent(max, val) {
            if (!max || !val) return 0;

            return ((val / max) * 100).toFixed(2);
        },
        addNewChoice() {
            this.formData.choices.push('');
        },
        removeChoice(i) {
            this.formData.choices.splice(i, 1);
        },
        createPoll() {
            axios.post(`poll`, this.formData, this.header)
                .then(res => {
                    this.create = false;

                    this.getPoll();
                })
                .catch(err => {
                    this.alert = err.response.data.message;

                    setTimeout(() => {
                        this.alert = '';
                    }, 2000);
                }); 
        },
        vote(poll, choice) {
            axios.post(`poll/${poll}/vote/${choice}`, {}, this.header)
                .then(res => {
                    this.getPoll();
                })
                .catch(err => {
                    console.log(err.response.data);
                }); 
        }
    },
    created() {
        this.getMe();
        this.getPoll();
    }
}
</script>

<style scoped>

.choices {
    border: 1px solid var(--blue);
    width: fit-content;
    border-radius: 5px;
}

.choice label {
    cursor: pointer;
}

.choice {
    padding: 7px 10px;
    text-align: center;
    border-bottom: 1px solid var(--blue);
}

.choice:last-child {
    border-bottom: none;
}

.button-rm {
    padding: 5px 15px;
}

.choice-group {
    display: flex;
    width: 100%;
    justify-content: space-between;
    margin-bottom: 10px;
}

.button-group {
    margin-top: 2rem;
}

.modal {
    position: fixed;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, .8);
    top: 0;
    left: 0;
    z-index: 100;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
    
form {
    background-color: white;
    border-radius: 10px;
    width: 400px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.res {
    margin-left: 1rem;
}

.result-group {
    margin-bottom: 1rem;
    align-items: center;
    display: flex;
}

.detail {
    margin-top: 5px;
    color: gray;
}

.title {
    margin: 0;
}

.poll {
    padding: 2rem;
    box-shadow: 0 0 15px rgba(0, 0, 0, .1);
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

header {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    height: 60px;
    color: white;
    background-color: var(--blue);
}

.fab {
    background-color: var(--blue);
    color: white;
    border: none;
    height: 50px;
    width: 50px;
    border-radius: 50%;
    box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
    position: fixed;
    right: 25px;
    bottom: 25px;
    font-size: 1rem;
    cursor: pointer;
}

.info {
    position: absolute;
    right: 20px;
    top: 50%;
    display: flex;
    transform: translateY(-50%);
}

.info span {
    margin: 0 5px;
}

.info * {
    margin: 0;
}

.info a {
    text-decoration: none;
    color: white;
}

.container {
    width: 85%;
    margin: auto;
    padding-top: 50px;
}
    
</style>