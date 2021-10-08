<template>
	<div class="login">
		<form @submit.prevent="reset">

			<div class="form-group">
				<label for="old_password">Old Password</label>	
				<input type="password" id="old_password" v-model="formData.old_password">
			</div>		

			<div class="form-group">
				<label for="new_password">New Password</label>	
				<input type="password" id="new_password" v-model="formData.new_password">
			</div>			

			<div class="form-group">
				<label for="confirm_password">Confirm Password</label>	
				<input type="password" id="confirm_password" v-model="formData.confirm_password">
			</div>			

			<button type="submit" class="primary">Reset</button>
		</form>

        <div class="alert" v-if="alert">{{ alert }}</div>
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
				old_password: '',
				new_password: '',
				confirm_password: ''
			},
            alert: ''
		}
	},
	methods: {
		reset() {
            if (this.formData.new_password != this.formData.confirm_password) {
                this.alert = 'Confirm Password not match';

                setInterval(() => {
                    this.alert = '';
                }, 2000);

                return;
            }

			axios.post(`auth/reset_password`, this.formData, this.header)
				.then(res => {
                    localStorage.removeItem('token');

                    this.$router.push('/');
				})
				.catch(err => {
					this.alert = err.response.data.message;
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