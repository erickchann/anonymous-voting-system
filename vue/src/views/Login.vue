<template>
	<div class="login">
		<form @submit.prevent="login">

			<div class="form-group">
				<label for="username">Username</label>	
				<input type="text" id="username" v-model="formData.username">
			</div>		

			<div class="form-group">
				<label for="password">Password</label>	
				<input type="password" id="password" v-model="formData.password">
			</div>			

			<button type="submit" class="primary">Login</button>
		</form>

		<div class="alert" v-if="alert">{{ alert }}</div>
	</div>
</template>

<script>
import axios from 'axios'

export default {
	data() {
		return {
			formData: {
				username: '',
				password: ''
			},
			alert: ''
		}
	},
	methods: {
		login() {
			axios.post(`auth/login`, this.formData)
				.then(res => {
					localStorage.setItem('token', res.data.access_token);

					if (this.formData.username == this.formData.password) {
                        this.$router.push('/reset');
                    } else {
                        this.$router.push('/home');
                    }
				})
				.catch(err => {
					this.alert = err.response.data.error;
				}); 
		}
	}
}
</script>

<style scoped>

.login {
	height: 100vh;
	width: 100vw;
	display: flex;
	align-items: center;
	justify-content: center;
}

button {
	margin-top: 2rem;
	font-size: 1rem;
}

form {
	width: 300px;
	display: flex;
	align-items: center;
	flex-direction: column;
}

</style>