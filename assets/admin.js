import{r as o,l as J,j as t,c as O}from"./index2.js";function U(){const[c,h]=o.useState([]),[m,_]=o.useState([]),[y,x]=o.useState(""),[a,w]=o.useState(""),[g,N]=o.useState(null),[j,I]=o.useState(""),[T,k]=o.useState(null);o.useEffect(()=>{const e=J({path:"/socket.io/",transports:["websocket","polling"],reconnectionAttempts:5});return b(),S(),e.on("connect",()=>{console.log("✅ Connected to WebSocket Server (ID:",e.id,")")}),e.on("refresh_tickets",r=>{console.log("📩 Real-time update received:",r),r.ticket_id&&(k(r.ticket_id),setTimeout(()=>k(null),3e3)),b()}),()=>{e.disconnect(),e.off("connect"),e.off("refresh_tickets")}},[]);async function u(e,r={}){const l=await fetch(e,{credentials:"include",...r}),p=await l.text();try{return{res:l,data:JSON.parse(p)}}catch{throw new Error("Invalid server response.")}}async function b(){try{const{res:e,data:r}=await u("/api/admin_list_tickets.php");if(!e.ok||r.success===!1)throw new Error(r.error||`HTTP ${e.status}`);h(r.tickets||[]),r.tier&&w(r.tier),r.current_user_id&&N(parseInt(r.current_user_id)),r.user_email&&I(r.user_email)}catch(e){x(`Load Error: ${e.message}`)}}async function S(){try{const{data:e}=await u("/api/list_developers.php");e.success&&_(e.developers||[])}catch{console.error("Could not load developers")}}async function d(e,r,l){const p=[...c],i=l===""?null:parseInt(l);h(n=>n.map(s=>s.id===e?{...s,[r]:i}:s));try{const{res:n,data:s}=await u("/api/patch_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:e,[r]:i})});if(!n.ok||s.success===!1)throw new Error(s.error||"Update failed")}catch(n){h(p),x(`Update failed: ${n.message}`)}}async function C(e){if(confirm("Delete this ticket permanently?"))try{const{data:r}=await u("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:e})});if(r.success)b();else throw new Error(r.error||"Delete failed")}catch(r){x(`Delete failed: ${r.message}`)}}const f={total:c.length,open:c.filter(e=>parseInt(e.status_id)===1).length,urgent:c.filter(e=>parseInt(e.priority_id)===4).length,unassigned:c.filter(e=>!e.assigned_to&&!e.claimed_by).length},E={Senior:m.filter(e=>e.role==="Senior"),Intermediate:m.filter(e=>e.role==="Intermediate"),Junior:m.filter(e=>e.role==="Junior"),Other:m.filter(e=>!["Senior","Intermediate","Junior"].includes(e.role))};return t.jsxs(t.Fragment,{children:[t.jsx("style",{children:`
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

        .header-flex {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 3rem;
        }

        .title-group {
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
        }

        h1 {
          font-size: 2.5rem;
          background: linear-gradient(to top, #ff0844, #ffb199);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .tier-badge {
          display: inline-block;
          align-self: flex-start;
          padding: 4px 12px;
          border-radius: 20px;
          font-size: 0.75rem;
          font-weight: bold;
          text-transform: uppercase;
          background: rgba(255, 255, 255, 0.05);
          color: #f77062;
          border: 1px solid #f77062;
          letter-spacing: 1px;
        }

        .logout-btn {
          background: #ff4d4d;
          color: white;
          padding: 10px 20px;
          text-decoration: none;
          border-radius: 4px;
          font-weight: bold;
        }

        .summary-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 1.5rem;
          margin-bottom: 3rem;
        }

        .stat-card {
          background: #1f1f1f;
          padding: 1.5rem;
          border-radius: 8px;
          text-align: center;
        }

        .stat-card h3 {
          font-size: 0.8rem;
          color: #aaa;
          margin-bottom: 0.5rem;
          text-transform: uppercase;
        }

        .stat-number {
          font-size: 2rem;
          font-weight: bold;
          color: #fe5196;
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
          border-left: 4px solid #333;
          transition: all 0.5s ease;
        }

        /* ✅ Real-time Glow Effect */
        .ticket.highlight {
          border-left-width: 10px;
          box-shadow: 0 0 20px rgba(254, 81, 150, 0.4);
          transform: scale(1.02);
          background: #1a1a1a;
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

        .assignment-indicator {
          font-size: 0.8rem;
          color: #2ecc71;
          margin-top: 5px;
          display: block;
          font-style: italic;
        }

        .controls-row { 
          display: flex; 
          gap: 10px; 
          margin-top: 1rem; 
          flex-wrap: wrap; 
          align-items: center;
        }

        select, button { 
          background: #1f1f1f; 
          border: 1px solid #333; 
          color: #fff; 
          padding: 8px; 
          border-radius: 4px; 
          cursor: pointer; 
        }

        select:disabled, button:disabled {
          opacity: 0.3;
          cursor: not-allowed;
          filter: grayscale(1);
        }

        .claim-btn {
          background: #2ecc71;
          color: #fff;
          font-weight: bold;
          border: none;
        }

        .release-btn {
          background: #e67e22;
          color: #fff;
          font-weight: bold;
          border: none;
        }

        .priority-tag { 
          font-size: 0.7rem; 
          padding: 2px 8px; 
          border-radius: 10px; 
          text-transform: uppercase; 
          margin-right: 10px; 
          vertical-align: middle; 
        }

        .danger { 
          color: #ff4d4d; 
          border-color: #442222; 
        }

        .error {
          color: #ff4d4d;
          margin-bottom: 1rem;
        }

        optgroup {
          background: #1f1f1f;
          color: #f77062;
          font-style: normal;
          font-weight: bold;
        }
      `}),t.jsxs("div",{className:"page",children:[t.jsxs("div",{className:"header-flex",children:[t.jsxs("div",{className:"title-group",children:[t.jsx("h1",{children:"IntelliResolver Ops"}),a&&t.jsxs("div",{className:"tier-badge",children:[a," Access Level"]})]}),t.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),t.jsxs("div",{className:"summary-grid",children:[t.jsxs("div",{className:"stat-card",children:[t.jsx("h3",{children:"Active"}),t.jsx("div",{className:"stat-number",children:f.total})]}),t.jsxs("div",{className:"stat-card",style:{borderTop:"3px solid #e74c3c"},children:[t.jsx("h3",{children:"Urgent"}),t.jsx("div",{className:"stat-number",children:f.urgent})]}),t.jsxs("div",{className:"stat-card",style:{borderTop:"3px solid #f1c40f"},children:[t.jsx("h3",{children:"Unassigned"}),t.jsx("div",{className:"stat-number",children:f.unassigned})]}),t.jsxs("div",{className:"stat-card",style:{borderTop:"3px solid #2ecc71"},children:[t.jsx("h3",{children:"Open"}),t.jsx("div",{className:"stat-number",children:f.open})]})]}),y&&t.jsx("div",{style:{color:"#ff4d4d",marginBottom:"1rem"},children:y}),t.jsx("div",{className:"card",children:c.map(e=>{const r=T===parseInt(e.id),l=!e.claimed_by&&(e.assigned_to===null||parseInt(e.assigned_to)===g)&&j!=="admin@intelliresolvers.com",p=parseInt(e.claimed_by)===g;return t.jsxs("div",{className:`ticket ${r?"highlight":""}`,style:{borderLeftColor:e.priority_color},children:[t.jsxs("div",{className:"ticket-header",children:[t.jsxs("div",{children:[t.jsx("span",{className:"priority-tag",style:{background:e.priority_color},children:e.priority_label}),t.jsxs("span",{className:"ticket-title",children:["#",e.id," — ",e.title]}),e.claimed_by_name?t.jsxs("span",{className:"assignment-indicator",children:["✓ Claimed by: ",e.claimed_by_name," ",parseInt(e.claimed_by)===g?"(You)":""]}):e.assigned_to_name?t.jsxs("span",{className:"assignment-indicator",style:{color:"#3498db"},children:["ℹ Assigned to: ",e.assigned_to_name]}):null]}),t.jsx("div",{style:{color:"#888",fontSize:"0.8rem"},children:e.email})]}),t.jsx("div",{style:{margin:"1rem 0",color:"#ccc",lineHeight:"1.6"},children:e.message}),t.jsxs("div",{className:"controls-row",children:[l&&t.jsx("button",{className:"claim-btn",onClick:()=>d(e.id,"claimed_by",g),children:"Claim Ticket"}),p&&t.jsx("button",{className:"release-btn",onClick:()=>d(e.id,"claimed_by",""),children:"Release Ticket"}),t.jsxs("select",{value:e.assigned_to||"",onChange:i=>d(e.id,"assigned_to",i.target.value),disabled:a==="Junior"||a==="Intermediate",children:[t.jsx("option",{value:"",children:"Unassigned"}),Object.entries(E).map(([i,n])=>n.length>0&&t.jsx("optgroup",{label:`${i} Tier`,children:n.map(s=>t.jsx("option",{value:s.id,children:s.name},s.id))},i))]}),t.jsxs("select",{value:e.status_id,onChange:i=>d(e.id,"status_id",i.target.value),disabled:j==="admin@intelliresolvers.com",children:[t.jsx("option",{value:"1",children:"Open"}),t.jsx("option",{value:"2",children:"In Progress"}),t.jsx("option",{value:"3",children:"Closed"})]}),t.jsxs("select",{value:e.priority_id,onChange:i=>d(e.id,"priority_id",i.target.value),disabled:a==="Junior"||a==="Intermediate",children:[t.jsx("option",{value:"1",children:"Low"}),t.jsx("option",{value:"2",children:"Medium"}),t.jsx("option",{value:"3",children:"High"}),t.jsx("option",{value:"4",children:"Urgent"})]}),t.jsx("button",{className:"danger",onClick:()=>C(e.id),disabled:a==="Junior"||a==="Intermediate",children:"Archive"})]})]},e.id)})})]})]})}const v=document.getElementById("root");v&&O.createRoot(v).render(t.jsx(U,{}));
