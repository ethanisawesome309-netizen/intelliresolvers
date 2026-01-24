import{r as a,j as e,a as p}from"./client.js";function f(){const[n,c]=a.useState([]),[o,s]=a.useState("");a.useEffect(()=>{i()},[]);async function i(){try{const t=await fetch("../api/admin_list_tickets.php",{credentials:"same-origin"});if(!t.ok)throw new Error;const r=await t.json();c(r),s("")}catch{s("Could not load tickets.")}}async function l(t,r){await fetch("../api/update_ticket_status.php",{method:"POST",headers:{"Content-Type":"application/json"},credentials:"same-origin",body:JSON.stringify({id:t,status:r})}),i()}async function m(t){confirm("Delete this ticket permanently?")&&(await fetch("../api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},credentials:"same-origin",body:JSON.stringify({id:t})}),i())}return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
        * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
          font-family: 'Kumbh Sans', sans-serif;
        }

        body {
          background: #141414;
          color: #fff;
        }

        .page {
          max-width: 1200px;
          margin: 0 auto;
          padding: 4rem 1rem;
        }

        h1 {
          text-align: center;
          font-size: 2.5rem;
          margin-bottom: 3rem;
          background: linear-gradient(to top, #ff0844, #ffb199);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        h2 {
          margin-bottom: 1.2rem;
          font-size: 1.4rem;
        }

        .card {
          background: #1f1f1f;
          border-radius: 8px;
          padding: 2rem;
          margin-bottom: 2.5rem;
        }

        .ticket {
          background: #141414;
          border-radius: 6px;
          padding: 1.2rem;
          margin-bottom: 1rem;
        }

        .ticket-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 0.4rem;
        }

        .ticket-title {
          font-weight: 600;
          background: linear-gradient(to top, #f77062, #fe5196);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .status {
          font-size: 0.75rem;
          padding: 4px 10px;
          border-radius: 999px;
          background: #1f1f1f;
          color: #f77062;
        }

        select, button {
          background: #141414;
          border: 1px solid #2a2a2a;
          color: #fff;
          padding: 6px 10px;
          border-radius: 6px;
        }

        .danger {
          color: #ff4d4d;
          cursor: pointer;
        }

        .error {
          color: #ff4d4d;
          margin-bottom: 1rem;
        }
      `}),e.jsxs("div",{className:"page",children:[e.jsx("h1",{children:"Admin Support Dashboard"}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"All Tickets"}),o&&e.jsx("div",{className:"error",children:o}),n.length===0?e.jsx("div",{className:"empty",children:"No tickets found."}):n.map(t=>e.jsxs("div",{className:"ticket",children:[e.jsxs("div",{className:"ticket-header",children:[e.jsxs("div",{className:"ticket-title",children:["#",t.id," — ",t.title]}),e.jsxs("select",{value:t.status,onChange:r=>l(t.id,r.target.value),children:[e.jsx("option",{children:"Open"}),e.jsx("option",{children:"In Progress"}),e.jsx("option",{children:"Closed"})]})]}),e.jsxs("div",{style:{marginBottom:"0.6rem",color:"#aaa"},children:["User: ",t.email]}),e.jsx("div",{children:t.message}),e.jsx("button",{className:"danger",style:{marginTop:"1rem"},onClick:()=>m(t.id),children:"Delete Ticket"})]},t.id))]})]})]})}const d=document.getElementById("root");d&&p(d).render(e.jsx(f,{}));
