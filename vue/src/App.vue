<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<style>

:root {
  --blue: #3f51b5;
  --red: #f44336;
}

* {
  box-sizing: border-box;
  font-family: sans-serif;
}

body {
  margin: 0;
  padding: 0;
}

.form-group {
  width: 100%;
  display: flex;
  flex-direction: column;
  margin-bottom: 1rem;
}

label {
  font-size: 14px;
  color: gray;
}

input[type=text], input[type=password], input[type=datetime-local] {
  border: none;
  border-bottom: 2px solid gray;
  transition: .4s;
  outline: none;
  padding: .5em 0;
  font-size: 1rem;
}

button.primary, button.danger {
  color: white;
  padding: 1em 2em;
  border: none;
  background-color: var(--blue);
  box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
  border-radius: 5px;
  cursor: pointer;
  margin: 0 10px;
}

button.danger {
  background-color: var(--red);
}

input[type=text]:focus, input[type=password]:focus {
  border-color: var(--blue);
}

.alert {
  position: absolute;
  left: 50%;
  bottom: 5%;
  transform: translateX(-50%);
  background-color: var(--red);
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
}

</style>
